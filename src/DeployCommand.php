<?php
/**
 * Deploy command
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\Deployer
 */

namespace Pronamic\Deployer;

use Acme\Command\DefaultCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Deploy command
 *
 * @author  Remco Tolsma
 * @version 1.0.0
 * @since   1.0.0
 */
class DeployCommand extends Command {
	/**
	 * Configure.
	 */
	protected function configure() {
		$this
			->setName( 'deploy' )
			->setDescription( 'Deploy.' )
			->setDefinition(
				new InputDefinition(
					array(
						new InputArgument( 'slug', InputArgument::REQUIRED ),
						new InputArgument( 'git', InputArgument::REQUIRED ),
						new InputArgument( 'main_file', InputArgument::OPTIONAL ),
					)
				)
			);
	}

	/**
	 * Execute.
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$io = new SymfonyStyle( $input, $output );

		$helper = $this->getHelper( 'process' );

		$xargs = Helper::get_gnu_xargs( $helper, $output );

		if ( false === $xargs ) {
			$io->error( 'Could not find GNU `xargs`, maybe try `brew install findutils`.' );

			return 1;
		}

		$grep = Helper::get_gnu_grep( $helper, $output );

		if ( false === $grep ) {
			$io->error( 'Could not find GNU `grep`, maybe try `brew install grep`.' );

			return 1;
		}

		$slug      = $input->getArgument( 'slug' );
		$git       = $input->getArgument( 'git' );
		$main_file = $input->getArgument( 'main_file' );

		if ( empty( $main_file ) ) {
			$main_file = sprintf( '%s.php', $slug );
		}

		$svn_url = sprintf(
			'https://plugins.svn.wordpress.org/%s',
			$slug
		);

		$relative_path_git   = 'git/' . $slug;
		$relative_path_build = 'build/' . $slug;
		$relative_path_zip   = 'zip/' . $slug;
		$relative_path_svn   = 'svn/' . $slug;

		$io->title( sprintf( 'Deploy `%s`', $slug ) );

		$io->table(
			array(
				'Key',
				'Value',
			),
			array(
				array( 'Slug', $slug ),
				array( 'Git', $git ),
				array( 'Main file', $main_file ),
				array( 'SVN URL', $svn_url ),
				array( 'SVN path', $relative_path_svn ),
				array( 'Git path', $relative_path_git ),
				array( 'Build path', $relative_path_build ),
				array( 'ZIP path', $relative_path_zip ),
			)
		);

		$process = new Process( array( 'git', 'pull' ), $relative_path_git );

		$helper->run( $output, $process );

		$process = new Process( array( 'git', 'checkout', 'master' ), $relative_path_git );

		$helper->run( $output, $process );

		$process = new Process( array( 'git', 'pull' ), $relative_path_git );

		$helper->run( $output, $process );

		$commands = array(
			sprintf(
				$grep . ' -i "Version:" %s',
				$relative_path_git . '/' . $main_file
			),
			sprintf(
				"awk -F '%s' '%s'",
				' ',
				'{print $NF}'
			),
			sprintf(
				"tr -d '%s'",
				'\r'
			),
		);

		$command = implode( ' | ', $commands );

		$process = new Process( $command );

		$helper->run( $output, $process );

		$version_main_file = trim( $process->getOutput() );

		$commands = array(
			sprintf(
				$grep . ' -i "Stable tag:" %s',
				$relative_path_git . '/readme.txt'
			),
			sprintf(
				"awk -F '%s' '%s'",
				' ',
				'{print $NF}'
			),
			sprintf(
				"tr -d '%s'",
				'\r'
			),
		);

		$command = implode( ' | ', $commands );

		$process = new Process( $command );

		$helper->run( $output, $process );

		$version_readme_txt = trim( $process->getOutput() );

		if ( $version_readme_txt !== $version_main_file ) {
			$io->error(
				sprintf(
					'Version in readme.txt & %s don\'t match. Exiting…',
					$main_file
				)
			);

			return 1;
		}

		$version = $version_main_file;

		// Subversion - Trunk
		$command = sprintf(
			'svn update %s --set-depth infinity',
			$relative_path_svn . '/trunk'
		);

		$process = new Process( $command );

		$helper->run( $output, $process );

		// Subversion - Assets
		$command = sprintf(
			'svn update %s --set-depth infinity',
			$relative_path_svn . '/assets'
		);

		$process = new Process( $command );

		$helper->run( $output, $process );

		// Composer
		if ( is_readable( $relative_path_git . '/composer.json' ) ) {
			$command = 'composer install --no-dev --prefer-dist --optimize-autoloader';

			$process = new Process( $command, $relative_path_git );

			$helper->run( $output, $process );
		}

		// Build - Empty build directory.
		$command = sprintf(
			'rm -r %s',
			$relative_path_build
		);

		$process = new Process( $command );

		$helper->run( $output, $process );

		// Build - Create build directory.
		$command = sprintf(
			'mkdir %s',
			$relative_path_build
		);

		$process = new Process( $command );

		$helper->run( $output, $process );

		// Build - Sync.
		$command = sprintf(
			'rsync --recursive --delete --exclude-from=exclude.txt --verbose %s %s',
			$relative_path_git . '/',
			$relative_path_build . '/'
		);

		$process = new Process( $command );

		$helper->run( $output, $process );

		// ZIP - Create ZIP directory.
		$command = sprintf(
			'mkdir %s',
			$relative_path_zip
		);

		$process = new Process( $command );

		$helper->run( $output, $process );

		// ZIP
		$relative_file_zip = $relative_path_zip . '/' . $slug . '.' . $version . '.zip';

		$command = sprintf(
			'zip --recurse-paths %s %s',
			realpath( $relative_file_zip ),
			'' . $slug . '/*'
		);

		$process = new Process( $command, 'build' );

		$helper->run( $output, $process );

		// AWS S3
		$io->section( '☁️  AWS S3' );

		$s3_link = sprintf(
			's3://downloads.pronamic.eu/plugins/%s/%s.%s.zip',
			$slug,
			$slug,
			$version
		);

		$command = sprintf(
			'aws s3 cp %s %s --acl public-read',
			$relative_file_zip,
			$s3_link
		);

		$process = new Process( $command );

		$helper->run( $output, $process );
	}
}