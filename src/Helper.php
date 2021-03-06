<?php
/**
 * Helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\Deployer
 */

namespace Pronamic\Deployer;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Helper
 *
 * @author  Remco Tolsma
 * @version 1.0.0
 * @since   1.0.0
 */
class Helper {
	/**
	 * Get GNU.
	 *
	 * @param
	 * @param
	 * @return string|false
	 */
	private static function get_gnu( $options, $identifier, $helper, OutputInterface $output ) {
		foreach ( $options as $option ) {
			$process = new Process( $option . ' --version' );

			$helper->run( $output, $process );

			$result = $process->getOutput();

			if ( false !== strpos( $result, $identifier ) ) {
				return $option;
			}
		}

		return false;
	}

	/**
	 * Get GNU xargs.
	 *
	 * @param
	 * @param
	 * @return string|false
	 */
	public static function get_gnu_xargs( $helper, OutputInterface $output ) {
		return self::get_gnu(
			array(
				'xargs',
				'gxargs',
			),
			'GNU findutils',
			$helper,
			$output
		);
	}

	/**
	 * Get GNU grep.
	 *
	 * @param
	 * @param
	 * @return string|false
	 */
	public static function get_gnu_grep( $helper, OutputInterface $output ) {
		return self::get_gnu(
			array(
				'grep',
				'ggrep',
			),
			'GNU grep',
			$helper,
			$output
		);
	}

	/**
	 * Get GNU cat.
	 *
	 * @param
	 * @param
	 * @return string|false
	 */
	public static function get_gnu_cat( $helper, OutputInterface $output ) {
		return self::get_gnu(
			array(
				'cat',
				'gcat',
			),
			'GNU coreutils',
			$helper,
			$output
		);
	}

	/**
	 * Get GNU cat.
	 *
	 * @param
	 * @param
	 * @return string|false
	 */
	public static function get_gnu_cut( $helper, OutputInterface $output ) {
		return self::get_gnu(
			array(
				'cut',
				'gcut',
			),
			'GNU coreutils',
			$helper,
			$output
		);
	}

	/**
	 * Get GNU tr.
	 *
	 * @param
	 * @param
	 * @return string|false
	 */
	public static function get_gnu_tr( $helper, OutputInterface $output ) {
		return self::get_gnu(
			array(
				'tr',
				'gtr',
			),
			'GNU coreutils',
			$helper,
			$output
		);
	}
}
