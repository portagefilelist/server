<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
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
$TemplateData['pageTitle'] = 'Find where does a file come from';
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

$_requestMode = "home";
if(isset($_GET['p']) && !empty($_GET['p'])) {
	$_requestMode = trim($_GET['p']);
	$_requestMode = Helper::validate($_requestMode,'nospace') ? $_requestMode : "home";

	if(!isset($_validPages[$_requestMode])) $_requestMode = "home";

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
if(!DEBUG) {
	file_put_contents($cacheFile,$content);
}

if(!DEBUG) {
	header("Pragma: public");
	header("Cache-Control: maxage=".CACHE_LIVETIME_SEC);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+CACHE_LIVETIME_SEC) . ' GMT');
}
header("Content-type: text/html; charset=UTF-8");

echo $content;
