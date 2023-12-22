#!/bin/bash
set -euo pipefail

TREEPATH="/path/to/tree";
DEST="/path/to";

rsync -ru --delete --no-motd --exclude='eclass' --exclude='licenses' --exclude='metadata' --exclude='profiles' --exclude='scripts' rsync://rsync.europe.gentoo.org/gentoo-portage/* ${TREEPATH};
cd $TREEPATH;
find . -name '*.ebuild' > "${DEST}/tree-flat-ebuild.txt"
