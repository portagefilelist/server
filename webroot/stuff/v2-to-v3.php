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

$categories = array();
$queryStr = "SELECT * FROM `pflv2_category`";
try {
    $query = $DB->query($queryStr);
    if($query !== false && $query->num_rows > 0) {
        while(($result = $query->fetch_assoc()) != false) {
            $categories[$result['hash']] = $result['name'];
        }
    }
} catch (Exception $e) {
    Helper::sysLog("[ERROR] Category list mysql catch: ".$e->getMessage());
    exit();
}


$queryStr = "SELECT hash, name, version, arch, category_id, lastmodified, importcount FROM `pflv2_package`";
try {
    $query = $DB->query($queryStr);
    if($query !== false && $query->num_rows > 0) {

        $packagesCount = 0;
        $pkgPerFile = 20;
        $i = 0;
        while(($result = $query->fetch_assoc()) != false) {
            $file = '/tmp/v3/importfile-'.$i.'.xml';

            if($packagesCount === 0) {
                // create file
                $content = '<?xml version="1.0" encoding="UTF-8"?><pfl xmlns="http://www.portagefilelist.de/xsd/collect">';
                file_put_contents($file, $content, LOCK_EX);
            }

            $content = '<category name="'.$categories[$result['category_id']].'">';
            $content .= '<package name="'.$result['name'].'" version="'.$result['version'].'" arch="'.$result['arch'].'" timestamp="'.time().'">';

            $queryFiles = $DB->query("SELECT hash, path FROM `pflv2_file` WHERE `package_id` = '".$DB->real_escape_string($result['hash'])."'");
            if($queryFiles !== false && $queryFiles->num_rows > 0) {
                $content .= '<files>';
                while(($resultFile = $queryFiles->fetch_assoc()) != false) {
                    $content .= '<file type="obj">'.$resultFile['path'].'</file>';
                }
                $content .= '</files>';
            }

            $queryUse = $DB->query("SELECT useword, package_id FROM `pflv2_package_use` WHERE `package_id` = '".$DB->real_escape_string($result['hash'])."'");
            if($queryUse !== false && $queryUse->num_rows > 0) {
                $content .= '<uses>';
                while(($resultUse = $queryUse->fetch_assoc()) != false) {
                    $content .= '<use>'.$resultUse['useword'].'</use>';
                }
                $content .= '</uses>';
            }

            $content .= '</package></category>';
            file_put_contents($file, $content, FILE_APPEND | LOCK_EX);

            $packagesCount++;
            if($packagesCount === $pkgPerFile) {
                $content = '</pfl>';
                file_put_contents($file, $content, FILE_APPEND | LOCK_EX);

                $packagesCount = 0;
                $i++;
            }
        }
    }
} catch (Exception $e) {
    Helper::sysLog("[ERROR] Package read mysql catch: ".$e->getMessage());
    exit();
}
