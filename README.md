# Deployer

## Subversion checkout

http://svnbook.red-bean.com/en/1.7/svn.ref.svn.c.checkout.html

```sh
svn co https://plugins.svn.wordpress.org/pronamic-ideal svn/pronamic-ideal
```

## Git checkout

https://www.git-scm.com/docs/git-clone

```sh
git clone https://github.com/pronamic/wp-pronamic-ideal.git git/pronamic-ideal
```

## Update

```sh
cd svn/pronamic-ideal

svn update

cd ../../
```

```sh
cd git/pronamic-ideal

git pull

cd ../../
```

## Checkout

```sh
cd git/pronamic-ideal

git checkout tags/5.4.1

composer install --no-dev --prefer-dist

cd ../../
```

## Build

```sh
rm -r build/pronamic-ideal

mkdir build/pronamic-ideal

rsync --recursive --delete --exclude-from=exclude.txt ./git/pronamic-ideal/ ./build/pronamic-ideal/
```

## To Subversion

```sh
rsync --recursive --delete ./build/pronamic-ideal/ ./svn/pronamic-ideal/trunk/

svn status ./svn/pronamic-ideal/trunk/ | grep '^!' | cut -c 9- | xargs -d '\n' -i svn delete {}@

svn status ./svn/pronamic-ideal/trunk/ | grep '^?' | cut -c 9- | xargs -d '\n' -i svn add {}@

svn commit ./svn/pronamic-ideal/trunk/ -m 'Update'
```

## Requirements

```sh
# GNU tools on Mac.
# https://www.topbug.net/blog/2013/04/14/install-and-use-gnu-command-line-tools-in-mac-os-x/

# GNU `cat`
brew install coreutils

# GNU `xargs`:
# https://superuser.com/questions/467176/replacement-for-xargs-d-in-osx
brew install findutils --with-default-names

# GNU `grep`:
# https://apple.stackexchange.com/questions/193288/how-to-install-and-use-gnu-grep-in-osx
brew install grep --with-default-names
```

## `svn status` and `xargs`


### `svn status`, `xargs` and `svn delete`

```sh
svn status | grep '^!' | cut -c 9- | xargs -d '\n' -i svn delete {}@
# ^^^^^^^^   ^^^^^^^^^   ^^^^^^^^^   ^^^^^^^^^^^^^^^^ ^^^^^^^^^^^^^^
#   |            |           |             |               |       |
#   |            |           |             |               |       \\ https://stackoverflow.com/questions/757435/how-to-escape-characters-in-subversion-managed-file-names
#   |            |           |             |               |       \\ http://svnbook.red-bean.com/en/1.7/svn.advanced.pegrevs.html
#   |            |           |             |               |
#   |            |           |             |               \\ Subversion delete.
#   |            |           |             |
#   |            |           |             \\ Xargs.
#   |            |           |
#   |            |           \\ Cut.
#   |            |           \\ https://github.com/apache/subversion/blob/1.10.2/subversion/svn/status.c#L447-L460
#   |            |
#   |            \\ Grep.
#   |
#   \\ Subversion status.
```

### `svn status`, `xargs` and `svn add`

```sh
svn status | grep '^?' | cut -c 9- | xargs -d '\n' -i svn add {}@
# ^^^^^^^^   ^^^^^^^^^   ^^^^^^^^^   ^^^^^^^^^^^^^^^^ ^^^^^^^^^^^
#   |            |           |             |               |    |
#   |            |           |             |               |    \\ https://stackoverflow.com/questions/757435/how-to-escape-characters-in-subversion-managed-file-names
#   |            |           |             |               |    \\ http://svnbook.red-bean.com/en/1.7/svn.advanced.pegrevs.html
#   |            |           |             |               |
#   |            |           |             |               \\ Subversion add.
#   |            |           |             |
#   |            |           |             \\ Xargs.
#   |            |           |
#   |            |           \\ Cut.
#   |            |           \\ https://github.com/apache/subversion/blob/1.10.2/subversion/svn/status.c#L447-L460
#   |            |
#   |            \\ Grep.
#   |
#   \\ Subversion status.
```

## Links

- https://www.topbug.net/blog/2013/04/14/install-and-use-gnu-command-line-tools-in-mac-os-x/
- https://superuser.com/questions/467176/replacement-for-xargs-d-in-osx
- https://apple.stackexchange.com/questions/193288/how-to-install-and-use-gnu-grep-in-osx
- https://github.com/stephenharris/grunt-wp-deploy
- https://github.com/GaryJones/wordpress-plugin-svn-deploy
- https://github.com/sudar/wp-plugin-in-github
- https://stackoverflow.com/questions/16991428/bash-how-to-put-each-line-within-quotation
