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
 * 2023 https://www.bananas-playground.net/projekt/portagefilelist/
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
