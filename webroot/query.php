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
 * 2023 - 2024 https://www.bananas-playground.net/projekt/portagefilelist/
 */

/**
 * The endpoint for the e-file
 * query.php?file=SEARCH_STRING
 */

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
ini_set('error_reporting',-1); // E_ALL & E_STRICT

require_once 'config.php';

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
require_once 'lib/helper.class.php';

# Loki
require_once 'lib/lokiclient.class.php';
$Loki = new Loki(LOKI_HOST, LOKI_PORT, array("app" => "pfl", "source" => "query"));

$returnData = array();

$_search = '';
if(isset($_GET['file']) && !empty($_GET['file'])) {
    if(DEBUG) Helper::sysLog("[DEBUG] query with : ".Helper::cleanForLog($_GET));
    $_search = trim($_GET['file']);
    $_search = Helper::validate($_search,'nospaceP') ? $_search : '';

    if(empty($_search)) {
        Helper::sysLog("[WARN] Invalid query GET : ".Helper::cleanForLog($_GET['file']));
    }
}

$_cachekey = 'q_'.md5(var_export($_GET,true));
$cacheFile = PATH_CACHE.'/'.$_cachekey;
if(file_exists($cacheFile) && !DEBUG) {
    header("Pragma: public");
    header("Cache-Control: maxage=".CACHE_LIVETIME_SEC);
    header('Expires: '.gmdate('D, d M Y H:i:s', time()+CACHE_LIVETIME_SEC).' GMT');
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo file_get_contents($cacheFile);

    $Loki->log("cacheview", array("cachekey" => $_cachekey, "value" => $_SERVER['QUERY_STRING']));
    $_l = $Loki->send();
    exit();
}

// still empty
if(empty($_search)) {
    $returnData['error']['code'] = 'NO_SEARCH_CRITERIA';
    $returnData['error']['message'] = 'No search criteria given or invalid input';

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo json_encode($returnData);
    $Loki->log("query.error", array("type" => "invalid"));
    $Loki->send();
    exit();
}

$queryOptions = array(
    'limit' => RESULTS_PER_PAGE
);

## DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_unicode_520_ci'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

require_once 'lib/files.class.php';
$Files = new Files($DB);

// do the search for the given request
$_search = strtolower($_search);
$Files->setQueryOptions($queryOptions);
if(!$Files->prepareSearchValue($_search)) {
    $returnData['error']['code'] = 'SEARCH_FAILED';
    $returnData['error']['message'] = 'Invalid search criteria. At least two (without wildcard) chars.';

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo json_encode($returnData);
    $Loki->log("query.error", array("type" => "invalid"));
    $Loki->send();
    exit();
}
$result = $Files->getFiles();

// search had an error
if(empty($result)) {
    $returnData['error']['code'] = 'SEARCH_FAILED';
    $returnData['error']['message'] = 'Invalid search criteria or nothing found. Either use a filename or complete path. Use * as a wildcard. Also check the path of the file. If the packagename is present the file is not recorded.';

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo json_encode($returnData);
    $Loki->log("query.error", array("type" => "empty"));
    $Loki->send();
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

        $_t['path'] = $entry['path'];
        $_t['category'] = $entry['categoryName'];
        $_t['package'] = $entry['packageName'];
        $_t['archs'] = array($entry['packageArch']);
        $_t['file'] = $entry['name'];
        $_t['version'] = $entry['packageVersion'];
        $_t['repository'] = $entry['packageRepo'];

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

$Loki->log("query.success");
$Loki->send();

exit();
