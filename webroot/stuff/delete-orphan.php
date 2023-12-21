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

$queryStr = "SELECT f.hash FROM `pflv3_file` AS f 
            LEFT JOIN pflv3_pkg2file AS p2f ON p2f.fileId = f.hash
            LEFT JOIN pflv3_package AS p ON p.hash = p2f.packageId
            WHERE p.name IS NULL";
try {
    $query = $DB->query($queryStr);
    if($query !== false && $query->num_rows > 0) {
        while(($result = $query->fetch_assoc()) != false) {
            $content = "DELETE FROM `pflv3_pkg2file` WHERE `fileId` = '".$result['hash']."';\n";
            $content .= "DELETE FROM `pflv3_file` WHERE `hash` = '".$result['hash']."';\n";
            file_put_contents("fix2.sql", $content, FILE_APPEND | LOCK_EX);
        }
    }
} catch (Exception $e) {
    Helper::sysLog("[ERROR] Category list mysql catch: ".$e->getMessage());
    exit();
}
