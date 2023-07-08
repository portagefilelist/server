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

# check inbox size
# currently abort if dir is larger then 1Gb
if(Helper::folderSize(PATH_INBOX) > 1000000000) {
	error_log("[ERROR] Upload inbox full!");
	exit();
}

if(isset($_FILES['foo'])) {
	$_uploadFile = $_FILES['foo'];

	error_log("[INFO] Upload starting upload with FILES: ".var_export($_FILES,true));

	if(isset($_uploadFile['name'])
		&& isset($_uploadFile['type'])
		&& isset($_uploadFile['size'])
		&& isset($_uploadFile['tmp_name'])
		&& isset($_uploadFile['error'])
	) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $_uploadFile['tmp_name']);
		finfo_close($finfo);
		if($mime != "application/x-bzip2") {
			error_log("[ERROR] Upload invalid mime type: ".var_export($mime,true));
			exit();
		}

		$_uploadTarget = tempnam(PATH_INBOX.'/','pfl');
		if(move_uploaded_file($_uploadFile['tmp_name'], $_uploadTarget)) {
			error_log("[INFO] Upload success. Target : ".var_export($_uploadTarget,true));
		}
		else {
			error_log("[ERRoR] Upload error while upload move: ".var_export($_FILES,true));
			exit();
		}

	} else {
		error_log("[ERROR] Upload incomplete FILES: ".var_export($_FILES,true));
	}
}
