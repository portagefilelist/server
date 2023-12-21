<?php

exit();

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
error_reporting(-1); // E_ALL & E_STRICT

## config
require_once('../config.php');

## set the error reporting
ini_set('log_errors',true);
if(DEBUG === true) {
    ini_set('display_errors',true);
}
else {
    ini_set('display_errors',false);
}

# time settings
date_default_timezone_set(TIMEZONE);

# static helper class
require_once '../lib/helper.class.php';

## DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_unicode_520_ci'");

# set time limit since it is long running
set_time_limit(300);

$queryStr = "SELECT p.hash, p.name,
            c.name AS categoryName,
            c.hash AS categoryId
            FROM `pflv3_package` AS p
            LEFT JOIN `pflv3_cat2pkg` AS c2p ON c2p.packageId = p.hash
            LEFT JOIN `pflv3_category` AS c ON c.hash = c2p.categoryId";
try {
    $query = $DB->query($queryStr);
    if($query !== false && $query->num_rows > 0) {
        while(($result = $query->fetch_assoc()) != false) {
            if(!file_exists('/home/banana/code/gentoo/'.$result['categoryName'].'/'.$result['name'])) {
                echo $result['hash']." ".$result['categoryName']."/".$result['name']." ".$result['categoryId']."\n";
                $content = "DELETE FROM `pflv3_cat2pkg` WHERE `packageId` = '".$result['hash']."';\n";
                $content .= "DELETE FROM `pflv3_package` WHERE `hash` = '".$result['hash']."';\n";
                $content .= "DELETE FROM `pflv3_package_use` WHERE `packageId` = '".$result['hash']."';\n";
                $content .= "DELETE FROM `pflv3_pkg2file` WHERE `packageId` = '".$result['hash']."';\n";

                file_put_contents("fix.sql", $content, FILE_APPEND | LOCK_EX);
            }
        }
    }
} catch (Exception $e) {
    Helper::sysLog("[ERROR] Category list mysql catch: ".$e->getMessage());
    exit();
}


// https://www.portagefilelist.de/index.php?p=package&id=06d297b2f0cd38d1a7448eaf3f592e41
