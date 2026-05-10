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
 * 2023 - 2026 https://www.bananas-playground.net/projekt/portagefilelist/
 */

/**
 * The endpoint for the e-file
 * query.php?file=SEARCH_STRING
 * query.php?package=SEARCH_STRING
 */

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
ini_set('error_reporting',-1); // E_ALL & E_STRICT

require_once 'config.php';

// set the error reporting
ini_set('log_errors',true);
if(DEBUG) {
    ini_set('display_errors',true);
}
else {
    ini_set('display_errors',false);
}

// time settings
date_default_timezone_set(TIMEZONE);

// static helper class
require_once 'lib/helper.class.php';

$returnData = array();

// we only accept two options and only one at a time
if(count($_GET) > 2) exit();
if(isset($_GET['file']) && isset($_GET['package'])) exit();
if(isset($_GET['file']) && count($_GET) > 1) exit();
if(isset($_GET['package']) && count($_GET) > 1) exit();
if(!isset($_GET['file']) && !isset($_GET['package'])) exit();

// if there will be any further additional options, this needs to be splitted and better seperated.
// see result building below
$_f_search = '';
$_p_search = '';
if(isset($_GET['file']) && !empty($_GET['file'])) {
    if(DEBUG) Helper::sysLog("[DEBUG] query with : ".Helper::cleanForLog($_GET));
    $_f_search = trim($_GET['file']);
    $_f_search = Helper::validate($_f_search,'nospaceP') ? $_f_search : '';

    if(empty($_f_search)) {
        Helper::sysLog("[WARN] Invalid query GET : ".Helper::cleanForLog($_GET['file']));
    }
} elseif(isset($_GET['package']) && !empty($_GET['package'])) {
    if(DEBUG) Helper::sysLog("[DEBUG] query with : ".Helper::cleanForLog($_GET));
    $_p_search = trim($_GET['package']);
    $_p_search = Helper::validate($_p_search,'nospaceP') ? $_p_search : '';

    if(empty($_p_search)) {
        Helper::sysLog("[WARN] Invalid query GET : ".Helper::cleanForLog($_GET['package']));
    }
}

$_cachekey = '_q_'.md5(var_export($_GET,true));
$cacheFile = PATH_CACHE.'/'.$_cachekey;
if(file_exists($cacheFile) && !DEBUG) {
    header("Pragma: public");
    header("Cache-Control: maxage=".CACHE_LIVETIME_SEC);
    header('Expires: '.gmdate('D, d M Y H:i:s', time()+CACHE_LIVETIME_SEC).' GMT');
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo file_get_contents($cacheFile);
    exit();
}

// still empty
if(empty($_f_search) && empty($_p_search)) {
    $returnData['error']['code'] = 'NO_SEARCH_CRITERIA';
    $returnData['error']['message'] = 'No search criteria given or invalid input';

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo json_encode($returnData);
    exit();
}

$queryOptions = array(
    'limit' => RESULTS_PER_PAGE
);

// DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_unicode_520_ci'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

require_once 'lib/files.class.php';
$SearchObj = new Files($DB);
$_searchTermn = strtolower($_f_search);
if(!empty($_p_search)) {
    require_once 'lib/packages.class.php';
    $SearchObj = new Packages($DB);
    $_searchTermn = strtolower($_p_search);
}

// do the search for the given request
$SearchObj->setQueryOptions($queryOptions);
if(!$SearchObj->prepareSearchValue($_searchTermn)) {
    $returnData['error']['code'] = 'SEARCH_FAILED';
    $returnData['error']['message'] = 'Invalid search criteria. At least two (without wildcard) or max. 100 chars.';

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo json_encode($returnData);
    exit();
}
$result = $SearchObj->helperSearch();

// search had an error
if(empty($result)) {
    $returnData['error']['code'] = 'SEARCH_FAILED';
    $returnData['error']['message'] = 'Invalid search criteria or nothing found. Use * as a wildcard.';

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo json_encode($returnData);
    exit();
}

$returnData['result'] = array();

if(isset($result['results'])) {
    foreach($result['results'] as $key=>$entry) {
        $_t = array( 'category' => '',
            'package' => '',
            'path' => '',
            'file' => '',
            'version' => '',
            'repository' => '',
            'archs' => array()
        );

        $_t['path'] = $entry['path']??'';
        $_t['category'] = $entry['categoryName'];
        $_t['package'] = $entry['packageName']??$entry['name'];
        $_t['archs'] = array($entry['packageArch']??$entry['arch']);
        $_t['file'] = $entry['name'];
        $_t['version'] = $entry['packageVersion']??$entry['version'];
        $_t['repository'] = $entry['packageRepo']??$entry['repository'];

        $returnData['result'][] = $_t;
    }
}

# "cache" the content
ob_start();

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
echo json_encode($returnData);

# output the content
$content = ob_get_contents();
ob_end_clean();

if(!DEBUG) {
    file_put_contents($cacheFile,$content);
    header("Pragma: public");
    header("Cache-Control: maxage=".CACHE_LIVETIME_SEC);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+CACHE_LIVETIME_SEC) . ' GMT');
}

echo $content;
exit();
