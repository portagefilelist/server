#!/bin/bash

PFL='/www/upload.portagefilelist.de'
TMP="${PFL}/tmp"
UPLOAD="${PFL}/upload"

SEMAPHORE="${PFL}/running.pid"

if [[ -f "${SEMAPHORE}" ]]; then
	echo "PFL Import is still running..." >&2
	exit
else
	echo "${BASHPID}" > "${SEMAPHORE}"
fi

for f in `ls -1 "${UPLOAD}"`; do
	if [[ ${f:${#f} - 8} != '.xml.bz2' ]]; then
		continue
	fi

	echo "importing ${f}"

	FILE=${f:0:${#f} - 4}
	/bin/bzip2 --decompress --stdout "${UPLOAD}/${f}" > "${TMP}/${FILE}"

	"${PFL}/bin/xml_import_psql.php" "${TMP}/${FILE}"

	# too many faulty uploads, just delete it
	rm -f "${UPLOAD}/${f}"
	rm -f "${TMP}/${FILE}"
done

rm -f "${SEMAPHORE}"
