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
ini_set('error_log',PATH_SYSTEMOUT.'/output.log');
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

$returnData = array();

$_search = '';
if(isset($_GET['file']) && !empty($_GET['file'])) {
	if(DEBUG) error_log("[DEBUG] query with : ".var_export($_GET, true));
	$_search = trim($_GET['file']);
	$_search = Helper::validate($_search,'nospaceP') ? $_search : '';

	if(DEBUG) {
		if(empty($_search)) {
			error_log("[DEBUG] Invalid query GET : ".var_export($_GET, true));
		}
	}
}
// still empty
if(empty($_search)) {
	$returnData['error']['code'] = 'NO_SEARCH_CRITERIA';
	$returnData['error']['message'] = 'No search criteria given or invalid input';

	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json');
	http_response_code(400);
	echo json_encode($returnData);
	exit();
}

$queryOptions = array(
	'limit' => RESULTS_PER_PAGE
);

## DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_bin'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

require_once 'lib/files.class.php';
$Files = new Files($DB);

// do the search for the given request
$_search = strtolower($_search);
$Files->setQueryOptions($queryOptions);
$result = $Files->getFiles($_search,false);

// search had an error
if(empty($result)) {
	$returnData['error']['code'] = 'SEARCH_FAILED';
	$returnData['error']['message'] = 'Invalid search criteria or nothing found. Either use a filename or complete path. Use * as a wildcard';

	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json');
	http_response_code(400);
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
			'archs' => array()
		);

		$_t['path'] = $entry['path'];
		$_t['category'] = $entry['categoryName'];
		$_t['package'] = $entry['packageName'];
		$_t['archs'] = array($entry['packageArch']);
		$_t['file'] = $entry['name'];
		$_t['version'] = $entry['packageVersion'];

		$returnData['result'][] = $_t;
	}
}


header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
echo json_encode($returnData);
exit();
