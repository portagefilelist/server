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
 * Why not use autoincrement in the mysql tables?
 * Using autoincrement and unique indexes mix not very well if ON DUPLICATE KEY is used
 * to avoid any duplicates and to retrieve the id with insert_id.
 */

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
ini_set('error_reporting',-1); // E_ALL & E_STRICT

## config
require_once('../config.php');

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

# get available files from inbox
$inboxFiles = glob(PATH_INBOX.'/*');
if(DEBUG) error_log('[DEBUG] Found files: '.Helper::cleanForLog($inboxFiles));

if(empty($inboxFiles)) {
	error_log('[INFO] Nothing in inbox.');
	exit();
}

error_log('[INFO] Importer starting.');

# static helper class
require_once 'lib/helper.class.php';

## DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_bin'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

libxml_use_internal_errors(true);

# set time limit since it is long running
set_time_limit(300);

$_fileCounter = count($inboxFiles);
if($_fileCounter < 5) {
	error_log('[INFO] Less then 5 files to import. Skipping for now.');
	exit();
}

foreach ($inboxFiles as $fileToImport) {

	$xmlReader = new XMLReader;

	// check mimetype
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($finfo, $fileToImport);
	finfo_close($finfo);
	if($mime != "application/x-bzip2") {
		error_log("[ERROR] Import invalid mime type: ".Helper::cleanForLog($mime));
		exit();
	}

	// decompress
	$fh = bzopen($fileToImport,'r');
	while(!feof($fh)) {
		$buffer = bzread($fh);
		if($buffer === FALSE) { error_log('[ERROR] Decompress read problem'); exit(); }
		if(bzerrno($fh) !== 0) { error_log('[ERROR] Decompress problem'); exit(); }
		file_put_contents($fileToImport.'.xml', $buffer, FILE_APPEND | LOCK_EX);
	}
	bzclose($fh);

	$fileToWorkWith = $fileToImport.'.xml';

	if (!$xmlReader->open($fileToWorkWith)) {
		error_log('[ERROR] Can not read xml file: '.Helper::cleanForLog($fileToWorkWith));
		continue;
	}

	// delete compressed file
	unlink($fileToImport);

	# validation does not work on the complete document
	# if the document is read in chunks
	# so the is valid call is while reading the file
	$xmlReader->setParserProperty(XMLReader::VALIDATE, true);
	$xmlReader->setSchema('schema.xsd');

	while ($xmlReader->read()) {

		if (!$xmlReader->isValid()) {
			$_xmlErrors = libxml_get_last_error();
			if ($_xmlErrors && $_xmlErrors instanceof libXMLError) {
				error_log('[ERROR] Invalid xml file: '.$_xmlErrors->message);
				libxml_clear_errors();
				continue;
			}
		}

		// only on element start
		if ($xmlReader->nodeType == XMLReader::ELEMENT) {
			switch ($xmlReader->name) {
				case 'category':
					$_cat = $xmlReader->getAttribute('name');
				break;

				case 'package':
					$_pack = $xmlReader->readOuterXml();;
				break;
			}

			// take only action if there is a category and a package
			// make sure to jump to the next category at the end
			if(!empty($_cat) && !empty($_pack)) {

				# the category insert query
				$_catID = md5($_cat);
				$queryCat = "INSERT INTO `".DB_PREFIX."_category` SET
								`name` = '".$DB->real_escape_string($_cat)."',
								`hash` = '".$DB->real_escape_string($_catID)."'
								ON DUPLICATE KEY UPDATE `lastmodified` = NOW()";
				if(QUERY_DEBUG) error_log('[QUERY] Category insert: '.Helper::cleanForLog($queryCat));

				# the package insert query
				$_packXML = new SimpleXMLElement($_pack);
				$_packID = md5((string)$_packXML['name'].(string)$_packXML['version'].(string)$_packXML['arch']);
				$queryPackage = "INSERT INTO `".DB_PREFIX."_package` SET
								`hash` = '".$DB->real_escape_string($_packID)."',
								`name` = '".$DB->real_escape_string((string)$_packXML['name'])."',
								`version` = '".$DB->real_escape_string((string)$_packXML['version'])."',
								`arch` = '".$DB->real_escape_string((string)$_packXML['arch'])."',
								`category_id` = '".$DB->real_escape_string($_catID)."'
								ON DUPLICATE KEY UPDATE `lastmodified` = NOW()";
				if(QUERY_DEBUG) error_log('[QUERY] Package insert: '.Helper::cleanForLog($queryPackage));

				if(empty($_catID) || empty($_packID)) {
					error_log("[ERROR] Missing category '$_catID' or package '$_packID' id");
					exit();
				}

				# the commit is at the "end"
				try {
					$DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

					$DB->query($queryCat);
					$DB->query($queryPackage);

				} catch (Exception $e) {
					$DB->rollback();
					error_log("[ERROR] Category or Package insert mysql catch: ".$e->getMessage());
					exit();
				}

				# now the package content
				foreach($_packXML->children() as $child) {
					switch ($child->getName()) {
						case 'uses':
							foreach($child->children() as $use) {
								$_useWord = (string)$use;

								# ignores
								# use expands
								if(strstr($_useWord,'_')) {
									continue;
								}

								if(!empty($_useWord) && !empty($_packID)) {
									$queryUses = "INSERT IGNORE INTO `".DB_PREFIX."_package_use` SET
												`useword` = '".$DB->real_escape_string($_useWord)."',
												`package_id` = '".$DB->real_escape_string($_packID)."'";
									if(QUERY_DEBUG) error_log('[QUERY] Use insert: '.Helper::cleanForLog($queryUses));
									try {
										$DB->query($queryUses);
									} catch (Exception $e) {
										$DB->rollback();
										error_log("[ERROR] Use insert mysql catch: ".$e->getMessage());
										exit();
									}
								}
							}
						break;

						case 'files':
							foreach($child->children() as $file) {
								$queryFile = "";
								$fileinfo = pathinfo((string)$file);
								$filename = $fileinfo['basename'];
								$path = (string)$file;

								# ignores
								# kernel sources
								# maybe hidden files like .ignores?
								if(strstr($path, '/usr/src/linux')
									|| strstr($path, '-gentoo-dist/')) {
									continue;
								}

								# change results in a needed rehash which takes a long time
								# this is why $file is not used here.
								$hash = md5($_packID.$_catID.$filename.$fileinfo['dirname']);

								switch((string) $file['type']) {
									case 'sym':
									case 'obj':
										$queryFile = "INSERT INTO `".DB_PREFIX."_file` SET
											`package_id` = '".$DB->real_escape_string($_packID)."',
											`name` = '".$DB->real_escape_string($filename)."',
											`path` = '".$DB->real_escape_string($path)."',
											`hash` = '".$DB->real_escape_string($hash)."'
											ON DUPLICATE KEY UPDATE `lastmodified` = NOW()";
									break;

									case 'dir':
									case 'fif':
									case 'dev':
									default:
										// nothing yet
								}
								if(!empty($queryFile)) {
									# if this is triggered often, make sure the DB col length is also increased.
									if(strlen($path) > 200) error_log('[WARNING] File path longer than 200 : '.Helper::cleanForLog($queryFile));
									if(QUERY_DEBUG) error_log('[QUERY] File insert: '.Helper::cleanForLog($queryFile));
									try {
										$DB->query($queryFile);
									} catch (Exception $e) {
										$DB->rollback();
										error_log("[ERROR] File insert mysql catch: ".$e->getMessage());
										exit();
									}
								}
							}
						break;
					}
				}

				try {
					$DB->commit();
				} catch (Exception $e) {
					$DB->rollback();
					error_log("[ERROR] Package commit mysql catch: ".$e->getMessage());
					exit();
				}

				unset($_cat);
				unset($_pack);
				unset($_catID);
				unset($_packID);
				unset($_packXML);

				$xmlReader->next("category");
			}
		}
	}
	$xmlReader->close();

	unlink($fileToWorkWith);
}

// file amount is already checked above. Avoids cleaning the cache if nothing is updated
Helper::recursive_remove_directory(PATH_CACHE, true);

error_log('[INFO] Importer imported '.$_fileCounter.' files');
error_log('[INFO] Importer ended.');
