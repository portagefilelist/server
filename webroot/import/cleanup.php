<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.
 *
 * pre 2023 https://github.com/tuxmainy
 * 2023 - 2025 https://www.bananas-playground.net/projekt/portagefilelist/
 */

/**
 * Do some cleanups.
 * Remove packages which are not in portage anymore.
 * Reclaim table space
 */

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
error_reporting(-1); // E_ALL & E_STRICT

## config
require_once('../config.php');

## set the error reporting
ini_set('log_errors',true);
if(DEBUG) {
    ini_set('display_errors',true);
}
else {
    ini_set('display_errors',false);
}

# time settings
date_default_timezone_set(TIMEZONE);

# static helper class
require_once '../lib/helper.class.php';

# import start secret is needed
$_check = '';
$argOptions = getopt('s:');
if(isset($argOptions['s']) && !empty($argOptions['s'])) {
    $_check = $argOptions['s'];
} elseif(isset($_GET['s']) && !empty($_GET['s'])) {
    $_check = $_GET['s'];
}

if($_check !== IMPORTER_SECRET) {
    exit();
}

Helper::sysLog('[INFO] Cleanup starting.');

## DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_unicode_520_ci'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

// get the packages to be removed since topicality is out of date, which tells us they are not in portage anymore
// first to create the archive files and later remove then. The foreign key does the rest.
$pidToRemove = array();
try {
    $queryStr = "SELECT `hash` FROM `".DB_PREFIX."_package` WHERE topicality IS NULL";
    if(QUERY_DEBUG) Helper::sysLog('[QUERY] Cleanup package query: '.Helper::cleanForLog($queryStr));
    $query = $DB->query($queryStr);
    if($query !== false && $query->num_rows > 0) {
        while(($result = $query->fetch_assoc()) != false) {
            $pidToRemove[$result['hash']] = $result['hash'];
        }
    }
} catch (Exception $e) {
    Helper::sysLog("[ERROR] Cleanup package query catch: ".$e->getMessage());
    exit();
}

if(empty($pidToRemove)) {
    Helper::sysLog('[INFO] Cleanup nothing to do');
    exit();
}

Helper::sysLog('[INFO] Cleanup '.count($pidToRemove).' packages');

Helper::sysLog('[INFO] Cleanup create historical packages');
require_once '../lib/package.class.php';
$Package = new Package($DB);
$_hisCount = 0;
foreach($pidToRemove as $k=>$v) {
    $package = $Package->getPackage($v);
    if(!empty($package)) {
        $_fileToWrite = ARCHIVE.'/'.$package['categoryName'].'/'.$package['name'].'-'.$package['version'].'.txt';
        if(file_exists($_fileToWrite)) continue;

        if(DEBUG) Helper::sysLog("[DEBUG] Cleanup writing file: ".Helper::cleanForLog($_fileToWrite));

        if(!is_dir(ARCHIVE.'/'.$package['categoryName'])) {
            mkdir(ARCHIVE.'/'.$package['categoryName'], 0755);
        }

        if (!$fp = fopen($_fileToWrite, 'w')) {
            Helper::sysLog("[ERROR] Cleanup can not create historical file: ".Helper::cleanForLog($_fileToWrite));
            exit;
        }

        $_pf = $Package->getPackageFiles($v);

        fwrite($fp, "Name: {$package['name']}\n");
        fwrite($fp, "Category: {$package['categoryName']}\n");
        fwrite($fp, "Version: {$package['version']}\n");
        fwrite($fp, "Repository: {$package['repository']}\n");
        fwrite($fp, "Files:\n");

        if(isset($_pf['results'])) {
            foreach($_pf['results'] as $key=>$entry) {
                fwrite($fp, "{$entry['path']}\n");
            }
        }

        fclose($fp);
        $_hisCount++;
    }
    unset($package);
}
Helper::sysLog('[INFO] Cleanup create '.$_hisCount.' historical packages done');

Helper::sysLog('[INFO] Cleanup package');
try {
    $queryStr = "DELETE FROM `".DB_PREFIX."_package` WHERE topicality IS NULL";
    if(QUERY_DEBUG) Helper::sysLog('[QUERY] Cleanup package query: '.Helper::cleanForLog($queryStr));
    $DB->query($queryStr);
} catch (Exception $e) {
    Helper::sysLog("[ERROR] Cleanup package query catch: ".$e->getMessage());
    exit();
}
Helper::sysLog('[INFO] Cleanup package done');

Helper::sysLog('[INFO] Cleanup file');
try {
    $queryStr = "DELETE f FROM `".DB_PREFIX."_file` AS f
                LEFT JOIN `".DB_PREFIX."_pkg2file` AS p2f ON p2f.fk_file = f.hash
                WHERE p2f.fk_file IS NULL";
    if(QUERY_DEBUG) Helper::sysLog('[QUERY] Cleanup file query: '.Helper::cleanForLog($queryStr));
    $DB->query($queryStr);
} catch (Exception $e) {
    Helper::sysLog("[ERROR] Cleanup package query catch: ".$e->getMessage());
    exit();
}
Helper::sysLog('[INFO] Cleanup file done');

// cleanup statslog table
Helper::sysLog('[INFO] Cleanup statslog');
try {
    $queryStr = "DELETE FROM `".DB_PREFIX."_statslog` WHERE timestmp < NOW() - INTERVAL 1 WEEK";
    if(QUERY_DEBUG) Helper::sysLog('[QUERY] Cleanup statslog query: '.Helper::cleanForLog($queryStr));
    $DB->query($queryStr);
} catch (Exception $e) {
    Helper::sysLog("[ERROR] Cleanup statslog query catch: ".$e->getMessage());
    exit();
}
Helper::sysLog('[INFO] Cleanup statslog done');

/*
// reclaim table space after cleanups
// effect may be minimal when used regulary
Helper::sysLog('[INFO] Cleanup reclaim table space');
try {
    $DB->query("ALTER TABLE `".DB_PREFIX."_statslog` ENGINE=InnoDB");
    $DB->query("ALTER TABLE `".DB_PREFIX."_package` ENGINE=InnoDB");
    $DB->query("ALTER TABLE `".DB_PREFIX."_pkg2file` ENGINE=InnoDB");
    $DB->query("ALTER TABLE `".DB_PREFIX."_file` ENGINE=InnoDB");
    $DB->query("ALTER TABLE `".DB_PREFIX."_cat2pkg` ENGINE=InnoDB");
    $DB->query("ALTER TABLE `".DB_PREFIX."_package_use` ENGINE=InnoDB");
} catch (Exception $e) {
    Helper::sysLog("[ERROR] Cleanup alter query catch: ".$e->getMessage());
    exit();
}
Helper::sysLog('[INFO] Cleanup reclaim table space done');
*/
Helper::sysLog('[INFO] Cleanup done');
