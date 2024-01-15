#!/bin/bash
set -euo pipefail

if [ $# -lt 1 ]; then
	echo "Missing file to work with"
	exit 2;
fi;

WORKFILE="$1"
RA="pfl-$RANDOM"
NEWFILENAME="$RA.xml"

touch ready/test
rm ready/*

echo "bunzip"
bunzip2 $WORKFILE

echo "lint"
xmllint --format ${WORKFILE}.out -o $NEWFILENAME

echo "split"
#xml_split -b $RA $NEWFILENAME
# if splitting by package you need different text added
xml_split -b $RA -l 2 $NEWFILENAME

rm *-00.xml

echo "adding"
for FIL in pfl-*; do
	#sed -i '1 a<pfl xmlns="http://www.portagefilelist.de/xsd/collect">' $FIL
	#echo -e "\n</pfl>" >> $FIL
	
	sed -i '1 a<pfl xmlns="http://www.portagefilelist.de/xsd/collect"><category name="sys-kernel">' $FIL
	echo -e "\n</category>\n</pfl>" >> $FIL
done

echo "zipping"
bzip2 ${RA}-*

echo "move"
mv ${RA}-* ready/
rm $NEWFILENAME

echo "done"
