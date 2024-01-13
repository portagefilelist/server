#!/bin/bash
set -euo pipefail

TREEPATH="/path/to/tree";
DEST="/path/to";

# gentoo
rsync -ru --delete --no-motd --exclude='eclass' --exclude='licenses' --exclude='metadata' --exclude='profiles' --exclude='scripts' rsync://rsync.europe.gentoo.org/gentoo-portage/* ${TREEPATH}/gentoo/
cd ${TREEPATH}/gentoo/
find . -name '*.ebuild' > "${DEST}/tree-gentoo-flat-ebuild.txt"

# guru
cd ${TREEPATH}/guru
git pull
find . -name '*.ebuild' > "${DEST}/tree-guru-flat-ebuild.txt"
