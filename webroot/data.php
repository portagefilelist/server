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
	Helper::sysLog("[ERROR] Upload inbox full!");
	exit();
}

if(isset($_FILES['foo'])) {
	$_uploadFile = $_FILES['foo'];

	Helper::sysLog("[INFO] Upload starting upload with FILES: ".Helper::cleanForLog($_FILES));

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
			Helper::sysLog("[ERROR] Upload invalid mime type: ".Helper::cleanForLog($mime));
			exit();
		}

		$_uploadTarget = tempnam(PATH_INBOX.'/','pfl');
		if(move_uploaded_file($_uploadFile['tmp_name'], $_uploadTarget)) {
			Helper::sysLog("[INFO] Upload success. Target : ".Helper::cleanForLog($_uploadTarget));
		}
		else {
			Helper::sysLog("[ERRoR] Upload error while upload move: ".Helper::cleanForLog($_FILES));
			exit();
		}

	} else {
		Helper::sysLog("[ERROR] Upload incomplete FILES: ".Helper::cleanForLog($_FILES));
	}
}
