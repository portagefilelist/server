<?php

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
ini_set('error_reporting',-1); // E_ALL & E_STRICT

## config
require_once('../config.php');

# time settings
date_default_timezone_set(TIMEZONE);

## MySQL DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_bin'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

## PostgreSQL Connection
$psqlConString = "dbname=pfl user=user password=test";
$pConn = pg_connect($psqlConString);

$queryStr = 'SELECT f.file, f.fileid, f.fk_pkgid,
				p.name AS "packageName",
				p.version AS "packageVersion",
				p.compiled AS "packageCompiled",
				c.name AS "categoryName",
				u.name AS "useFlagName",
				a.name AS "archName"
			FROM "pkg" AS p
			LEFT JOIN "dir" AS c ON c.dirid = p.fk_dirid
			LEFT JOIN "file" AS f ON f.fk_pkgid = p.pkgid
			LEFT JOIN "file2useflag" AS f2u ON f2u.fk_fileid = f.fileid
			LEFT JOIN "useflag" AS u ON u.useflagid = f2u.fk_useflagid
			LEFT JOIN "file2arch" AS f2a ON f2a.fk_fileid = f.fileid
			LEFT JOIN "arch" AS a ON a.archid = f2a.fk_archid
			WHERE f.misc = \'obj\'
			ORDER BY p.compiled
			LIMIT 500';
$query = pg_query($pConn, $queryStr);
while ($row = pg_fetch_assoc($query)) {

	$_catID = md5($row['categoryName']);
	$queryCat = "INSERT IGNORE INTO `".DB_PREFIX."_category` SET
					`name` = '".$DB->real_escape_string($row['categoryName'])."',
					`hash` = '".$DB->real_escape_string($_catID)."'";
	var_dump($queryCat);

	$_packID = md5($row['packageName'].$row['packageVersion'].$row['archName']);
	$queryPackage = "INSERT IGNORE INTO `".DB_PREFIX."_package` SET 
					`hash` = '".$DB->real_escape_string($_packID)."',
					`name` = '".$DB->real_escape_string($row['packageName'])."',
					`version` = '".$DB->real_escape_string($row['packageVersion'])."',
					`arch` = '".$DB->real_escape_string($row['archName'])."',
					`category_id` = '".$DB->real_escape_string($_catID)."'";
	var_dump($queryPackage);

	$fileinfo = pathinfo($row['file']);
	$filename = $fileinfo['basename'];
	$path = $fileinfo['dirname'];

	$fileHash = md5($_packID.$_catID.$filename.$path);
	$queryFile = "INSERT IGNORE INTO `".DB_PREFIX."_file` SET
					`package_id` = '".$DB->real_escape_string($_packID)."',
					`name` = '".$DB->real_escape_string($filename)."',
					`path` = '".$DB->real_escape_string($path)."',
					`hash` = '".$DB->real_escape_string($fileHash)."'";
	var_dump($queryFile);

	$queryUses = '';
	if(!empty($row['useFlagName'])) {
		$queryUses = "INSERT IGNORE INTO `".DB_PREFIX."_package_use` SET
						`useword` = '".$DB->real_escape_string($row['useFlagName'])."',
						`package_id` = '".$DB->real_escape_string($_packID)."'";
		var_dump($queryUses);
	}

	try {
		$DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

		$DB->query($queryCat);
		$DB->query($queryPackage);
		$DB->query($queryFile);
		if(!empty($queryUses)) {
			$DB->query($queryUses);
		}

		$DB->commit();
	} catch (Exception $e) {
		$DB->rollback();
		error_log("mysql catch: ".$e->getMessage());
		exit();
	}
}
