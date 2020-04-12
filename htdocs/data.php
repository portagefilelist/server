<?php

#	error_reporting(E_ALL);

	#$filename = $_FILES['foo']['name'];
	$guid = trim(file_get_contents('/proc/sys/kernel/random/uuid'));
	$suffix = '.pfl.xml';
	$filename = "$guid$suffix.bz2";
	$filenameu = "$guid$suffix";
	$now = time();
	$upload = '/www/upload.portagefilelist.de/upload';
	$uploadf = "$upload/$now-$filename";
	$uploadt = '/www/upload.portagefilelist.de/uploadtest';
	$uploadtf = "$uploadt/$now-$filename";
	$uploadtfu = "$uploadt/$now-$filenameu";
	$schema = '/www/upload.portagefilelist.de/htdocs/schema/collect.xsd';

	if (move_uploaded_file($_FILES['foo']['tmp_name'], $uploadf)) {
		if (array_key_exists('test', $_REQUEST)) {
			rename($uploadf, $uploadtf);
			system("bunzip2 \"$uploadtf\"");
			system("xmllint --format --schema \"$schema\" \"$uploadtfu\" 2>&1");
			unlink($uploadtfu);
			die("uploaded file was deleted because of test/debug flag\n");
		}
	} else {
		http_response_code(500);
		die('unkown error while copying file');
	}

?>
