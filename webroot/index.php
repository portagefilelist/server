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

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
error_reporting(-1); // E_ALL & E_STRICT

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

# simple cache based on get
$_cid = '';
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $_cid = trim($_GET['id']);
    $_cid = Helper::validate($_cid,'nospace') ? $_cid : '';
}
$_cachekey = $_cid.'_'.md5(var_export($_GET,true));
$cacheFile = PATH_CACHE.'/'.$_cachekey;
if(file_exists($cacheFile) && !DEBUG) {
    header("Pragma: public");
    header("Cache-Control: maxage=".CACHE_LIVETIME_SEC);
    header('Expires: '.gmdate('D, d M Y H:i:s', time()+CACHE_LIVETIME_SEC).' GMT');
    header("Content-type: text/html; charset=UTF-8");
    echo file_get_contents($cacheFile);

    exit();
}

# template vars
$TemplateData = array();
$TemplateData['pagination'] = array();
$TemplateData['pageTitle'] = 'Find where does a file come from on a Gentoo install';
$messageData = array();

# the view
$View = 'view/home/home.php';
# the script
$ViewScript = 'view/home/home.inc.php';
# the messages
$ViewMessage = 'view/system/message.php';
# the menu
$ViewMenu = 'system/menu.php';
# valid includes
$_validPages["home"] = "home";
$_validPages["imprint"] = "imprint";
$_validPages["about"] = "about";
$_validPages["package"] = "package";
$_validPages["category"] = "category";
$_validPages["packages"] = "packages";
$_validPages["categories"] = "categories";
$_validPages["stats"] = "stats";
$_validPages["archive"] = "archive";

$_requestMode = "home";
if(isset($_GET['p']) && !empty($_GET['p'])) {
    $_requestMode = trim($_GET['p']);
    $_requestMode = Helper::validate($_requestMode,'nospace') ? $_requestMode : "home";

    if(!isset($_validPages[$_requestMode])) {
        header("Location: 404.php");
        exit();
    }

    $ViewScript = 'view/'.$_requestMode.'/'.$_requestMode.'.inc.php';
    $View = 'view/'.$_requestMode.'/'.$_requestMode.'.php';
}

## DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_unicode_520_ci'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

# "cache" the content
ob_start();

# now include the script
# this sets information into $Data and can overwrite $View
if(!empty($ViewScript) && file_exists($ViewScript)) {
    require_once $ViewScript;
}

## now inlcude the main view
require_once 'view/main.php';

# output the content
$content = ob_get_contents();
ob_end_clean();

$_tocache = true;
if((isset($messageData['status']) && ($messageData['status'] == "danger" || $messageData['status'] == "warning")) || DEBUG) {
    $_tocache = false;
}

if($_tocache) {
    file_put_contents($cacheFile,$content);
    header("Pragma: public");
    header("Cache-Control: maxage=".CACHE_LIVETIME_SEC);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+CACHE_LIVETIME_SEC) . ' GMT');
}
header("Content-type: text/html; charset=UTF-8");

if(isset($messageData['statusCode'])) {
    http_response_code($messageData['statusCode']);
}

echo $content;
