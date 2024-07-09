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
 * pre 2023 - https://github.com/tuxmainy
 * 2023 - 2024 https://www.bananas-playground.net/projekt/portagefilelist/
 */

/**
 * get a flat ebuild list from external source.
 * Use this list to update the packages topicality info.
 * Everything not updated from last run is considered not in the tree anymore since the flat ebuild list
 * contains all the current ebuilds
 */

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

# Loki
require_once '../lib/lokiclient.class.php';
$Loki = new Loki(LOKI_HOST, LOKI_PORT, array("app" => "pfl", "source" => "topicality"));

if(DEBUG) Helper::sysLog('[DEBUG] Topicality importer starting.');

## DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_unicode_520_ci'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

$ebuildFiles = array(
    'gentoo' => array(
        'file' => PATH_ABSOLUTE.'/import/tree-gentoo-flat-ebuild.txt',
        'url' => 'http://91.132.146.200/webroot/tree-gentoo-flat-ebuild.txt'
    ),
    'guru' => array(
        'file' => PATH_ABSOLUTE.'/import/tree-guru-flat-ebuild.txt',
        'url' => 'http://91.132.146.200/webroot/tree-guru-flat-ebuild.txt'
    )
);

foreach($ebuildFiles as $repo=>$info) {
    Helper::downloadFile($info['url'], $info['file']);
    if(!file_exists($info['file'])) {
        Helper::sysLog('[WARNING] Can not download topicality file. '.$info['url']);
        Helper::notify("Can not download topicality file: ".$info['url']);
    }
}

// those are sprintf placeholders, not prepared query stuff
$queryStrTemplate = "UPDATE `".DB_PREFIX."_package` AS p 
                    LEFT JOIN `".DB_PREFIX."_cat2pkg` AS c2p ON c2p.packageId = p.hash 
                    LEFT JOIN `".DB_PREFIX."_category` AS c ON c.hash = c2p.categoryId
                    SET p.topicality = CURDATE(), p.topicalityLastSeen = CURDATE()
                    WHERE p.name = '%s' AND c.name = '%s' AND p.version = '%s' AND p.repository = '%s'";
$updateCounter = 0;
$DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
// the package and category files which are updated
// and then used in cache clean
$_upId = array();

foreach($ebuildFiles as $repo=>$info) {
    if(!file_exists($info['file'])) continue;

    $file = new SplFileObject($info['file']);
    while (!$file->eof()) {
        $line = trim($file->fgets());
        $line = str_replace('./', '',$line);

        if(substr_count($line, '/') == 2) {
            # sys-process/systemd-cron/systemd-cron-2.3.0-r1.ebuild
            $_t = explode('/', $line);
            $_version = $_t[2];
            $_version = str_replace('.ebuild', '', $_version);
            $_version = str_replace($_t[1].'-', '', $_version);

            if(!empty($_t[0]) && !empty($_t[1]) && $_version != '') {
                $queryStr =  sprintf($queryStrTemplate, $_t[1], $_t[0], $_version, $repo);
                if(QUERY_DEBUG) Helper::sysLog('[QUERY] Topicality update: '.Helper::cleanForLog($queryStr));
                if(DEBUG) Helper::sysLog("[DEBUG] Topicality update: ".Helper::cleanForLog($_t));
                try {
                    $query = $DB->query($queryStr);
                    if($DB->affected_rows > 0) {
                        $updateCounter++;
                    }
                } catch (Exception $e) {
                    Helper::sysLog("[ERROR] Topicality update catch: ".$e->getMessage());
                    $Loki->log("import.error", array("type" => "query"));
                    $Loki->send();
                    exit();
                }
            }
        }
    }
    $file = null;
}

if($updateCounter > 0) {
    try {
        $queryStr = "UPDATE `".DB_PREFIX."_package` SET topicality = NULL WHERE topicality <> CURDATE()";
        if(QUERY_DEBUG) Helper::sysLog('[QUERY] Topicality update old: '.Helper::cleanForLog($queryStr));
        $DB->query($queryStr);
        $DB->commit();

        // to invalidate the cache we need the packageId which is not known since the update goes by non id values.
        $queryStr = "SELECT `hash` FROM `".DB_PREFIX."_package` WHERE topicality = CURDATE()";
        $query = $DB->query($queryStr);
        if(QUERY_DEBUG) Helper::sysLog('[QUERY] Topicality select updated: '.Helper::cleanForLog($queryStr));
        if($query !== false && $query->num_rows > 0) {
            while(($result = $query->fetch_assoc()) != false) {
                $_upId[$result['hash']] = $result['hash'];
            }
        }
    } catch (Exception $e) {
        $DB->rollback();
        Helper::sysLog("[ERROR] Topicality update catch: ".$e->getMessage());
        $Loki->log("import.error", array("type" => "query"));
        $Loki->send();
        exit();
    }

    $cacheFiles = glob(PATH_CACHE.'/*_*');
    $_toDiff = array();
    foreach($cacheFiles as $cf) {
        $_paths = explode("/", $cf);
        $_filename = array_pop($_paths);
        $_fileParts = explode("_",$_filename);
        // use filepath as the key. intersect gets the key
        $_toDiff[$cf] = $_fileParts[0];
    }
    $toDelete = array_intersect($_toDiff, $_upId);
    if(!empty($toDelete)) {
        foreach($toDelete as $k=>$v) {
            unlink($k);
        }
        Helper::sysLog('[INFO] Topicality importer purged id '.count($toDelete).' files');
        $Loki->log("import.purged", array("value" => strval(count($toDelete))));
    }
}

Helper::sysLog('[INFO] Topicality importer updated: '.$updateCounter);
Helper::sysLog('[INFO] Topicality importer ended.');
$Loki->log("import.updated", array("value" => strval($updateCounter)));

$cacheFilesI = new FilesystemIterator(PATH_CACHE, FilesystemIterator::SKIP_DOTS);
$Loki->log("import.cachefiles", array("amount" => strval(iterator_count($cacheFilesI))));

$_l = $Loki->send();
if(DEBUG) Helper::sysLog("[DEBUG] loki send ".$_l);
