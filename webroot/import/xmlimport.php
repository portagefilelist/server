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

/**
 * Why not use autoincrement in the mysql tables?
 * Using autoincrement and unique indexes mix not very well if ON DUPLICATE KEY is used
 * to avoid any duplicates and to retrieve the id with insert_id.
 */

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
error_reporting(-1); // E_ALL & E_STRICT

// config
require_once('../config.php');

// set the error reporting
ini_set('log_errors',true);
if(DEBUG === true) {
    ini_set('display_errors',true);
}
else {
    ini_set('display_errors',false);
}

// time settings
date_default_timezone_set(TIMEZONE);

// static helper class
require_once '../lib/helper.class.php';

// import start secret is needed
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

// get available files from inbox
$inboxFiles = glob(PATH_INBOX.'/pfl*');
if(DEBUG) Helper::sysLog('[DEBUG] Found files: '.Helper::cleanForLog($inboxFiles));

if(empty($inboxFiles)) {
    Helper::sysLog('[INFO] Nothing in inbox.');
    exit();
}
$_fileCounter = count($inboxFiles);
if($_fileCounter < 5) {
    Helper::sysLog('[INFO] Less then 5 files to import. Skipping for now.');
    exit();
}

Helper::sysLog('[INFO] Importer starting.');

// DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_unicode_520_ci'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

libxml_use_internal_errors(true);

// set time limit since it is long running
set_time_limit(300);

// the package and category files which are updated
// and then used in cache clean
$_upId = array();

foreach ($inboxFiles as $fileToImport) {

    // check mimetype
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fileToImport);
    finfo_close($finfo);
    // can be cleaned up with future vesions. Tar is the new one.
    if($mime != "application/x-bzip2" && $mime != "application/x-tar") {
        Helper::sysLog("[WARNING] Import invalid mime type: ".Helper::cleanForLog($mime));
        rename($fileToImport, PATH_INBOX.'/invalidMimeType-'.time());
        continue;
    }

    // if tar extract it and continue. Extracted files will be processed next run.
    if($mime == "application/x-tar") {
        $_tarFile = $fileToImport.'.tar';
        try {
            // stupid PharData works on file suffix ...
            rename($fileToImport, $_tarFile);
            $phar = new PharData($_tarFile);
            $phar->extractTo(PATH_INBOX);
            Helper::sysLog('[INFO] Importer extracted tar file: '.Helper::cleanForLog($_tarFile));
            unlink($_tarFile);
            continue;
        } catch (Exception $e) {
            Helper::sysLog("[ERROR] Import can not extract tar file: ".Helper::cleanForLog($e->getMessage()));
            rename($_tarFile, PATH_INBOX.'/invalidTar-'.time());
            continue;
        }
    }

    // decompress
    $fh = bzopen($fileToImport,'r');
    $_unpackCounter = 1024; // default value for read bytes at bzread
    $_unpackSizeMark = false;
    while(!feof($fh)) {
        $buffer = bzread($fh);
        if($buffer === FALSE) { Helper::sysLog('[ERROR] Decompress read problem'); exit(); }
        if(bzerrno($fh) !== 0) { Helper::sysLog('[ERROR] Decompress problem'); exit(); }
        file_put_contents($fileToImport.'.xml', $buffer, FILE_APPEND | LOCK_EX);
        $_unpackCounter += 1024;
        if($_unpackCounter > 300000000) { // 300MB max unpack size
            $_unpackSizeMark = true;
            break;
        }
    }
    bzclose($fh);

    if($_unpackSizeMark) {
        Helper::sysLog('[WARNING] Max unpack filesize reached: '.Helper::cleanForLog($fileToImport));
        rename($fileToImport, PATH_INBOX.'/invalidSize-'.time());
        unlink($fileToImport.'.xml');
        continue;
    }

    $fileToWorkWith = $fileToImport.'.xml';

    $xmlReader = new XMLReader;

    if (!$xmlReader->open($fileToWorkWith)) {
        Helper::sysLog('[WARNING] Can not read xml file: '.Helper::cleanForLog($fileToWorkWith));
        rename($fileToImport, PATH_INBOX.'/invalidFile-'.time());
        continue;
    }

    // delete compressed file
    unlink($fileToImport);

    // validation does not work on the complete document
    // https://www.php.net/manual/en/xmlreader.isvalid.php
    // if the document is read in chunks
    // so the is valid call is while reading the file
    $xmlReader->setParserProperty(XMLReader::VALIDATE, true);
    $xmlReader->setSchema('schema.xsd');

    while ($xmlReader->read()) {

        if (!$xmlReader->isValid()) {
            $_xmlErrors = libxml_get_last_error();
            if ($_xmlErrors && $_xmlErrors instanceof libXMLError) {
                Helper::sysLog('[WARNING] Invalid xml file: '.$_xmlErrors->message);
                libxml_clear_errors();
                rename($fileToWorkWith, PATH_INBOX.'/invalidXMLFile-'.time());
                break;
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

                // the category insert query
                $_catID = md5($_cat);
                $queryCat = "INSERT INTO `".DB_PREFIX."_category` SET
                                `name` = '".$DB->real_escape_string($_cat)."',
                                `hash` = '".$DB->real_escape_string($_catID)."'
                                ON DUPLICATE KEY UPDATE `lastmodified` = NOW()";
                if(QUERY_DEBUG) Helper::sysLog('[QUERY] Category insert: '.Helper::cleanForLog($queryCat));

                // the package insert query
                $_packXML = new SimpleXMLElement($_pack);

                $_repo = 'gentoo';
                if(!empty((string)$_packXML['repo'])) {
                    $_repo = (string)$_packXML['repo'];
                }
                $_packID = md5($_cat.(string)$_packXML['name'].(string)$_packXML['version'].(string)$_packXML['arch'].$_repo);
                // to keep existing ids the same. repo is an addition to existing data.
                if($_repo == "gentoo") {
                    $_packID = md5($_cat.(string)$_packXML['name'].(string)$_packXML['version'].(string)$_packXML['arch']);
                }
                $_packageName = (string)$_packXML['name'];
                $queryPackage = "INSERT INTO `".DB_PREFIX."_package` SET
                                `hash` = '".$DB->real_escape_string($_packID)."',
                                `name` = '".$DB->real_escape_string($_packageName)."',
                                `version` = '".$DB->real_escape_string((string)$_packXML['version'])."',
                                `arch` = '".$DB->real_escape_string((string)$_packXML['arch'])."',
                                `repository` = '".$DB->real_escape_string($_repo)."'
                                ON DUPLICATE KEY UPDATE `lastmodified` = NOW(), `importcount` = `importcount` + 1";
                if(QUERY_DEBUG) Helper::sysLog('[QUERY] Package insert: '.Helper::cleanForLog($queryPackage));

                // packageId does contain the category and not only the package name
                $queryCat2Pkg = "INSERT IGNORE INTO `".DB_PREFIX."_cat2pkg` SET
                                    `categoryId` = '".$DB->real_escape_string($_catID)."',
                                    `packageId` = '".$DB->real_escape_string($_packID)."'";
                if(QUERY_DEBUG) Helper::sysLog('[QUERY] Package _cat2pkg insert: '.Helper::cleanForLog($queryCat2Pkg));

                if(empty($_catID) || empty($_packID)) {
                    Helper::sysLog("[WARNING] Missing category '$_catID' or package '$_packID' id in file: ".$fileToWorkWith);
                    continue;
                }

                // the commit is at the "end"
                try {
                    $DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

                    $DB->query($queryCat);
                    $DB->query($queryPackage);
                    $DB->query($queryCat2Pkg);

                } catch (Exception $e) {
                    $DB->rollback();
                    Helper::sysLog("[ERROR] Category or Package insert mysql catch: ".$e->getMessage());
                    exit();
                }

                // now the package content
                foreach($_packXML->children() as $child) {
                    switch ($child->getName()) {
                        case 'uses':
                            foreach($child->children() as $use) {
                                $_useWord = (string)$use;

                                // ignores
                                // use expands
                                if(strstr($_useWord,'_')) {
                                    continue;
                                }

                                if(!empty($_useWord) && !empty($_packID)) {
                                    $queryUses = "INSERT IGNORE INTO `".DB_PREFIX."_package_use` SET
                                                `useword` = '".$DB->real_escape_string($_useWord)."',
                                                `packageId` = '".$DB->real_escape_string($_packID)."'";
                                    if(QUERY_DEBUG) Helper::sysLog('[QUERY] Use insert: '.Helper::cleanForLog($queryUses));
                                    try {
                                        $DB->query($queryUses);
                                    } catch (Exception $e) {
                                        $DB->rollback();
                                        Helper::sysLog("[ERROR] Use insert mysql catch: ".$e->getMessage());
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

                                // ignores
                                // kernel sources, dist kernel
                                // __ which are often __pycache and other testfiles
                                if(strstr($path, '/usr/src/linux')
                                    || strstr($path, '-gentoo-dist/')
                                    || strstr($path, '__')
                                    ) {
                                    continue;
                                }

                                $hash = md5($path);

                                switch((string) $file['type']) {
                                    case 'sym':
                                    case 'obj':
                                        $queryFile = "INSERT INTO `".DB_PREFIX."_file` SET
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
                                    $queryPgk2File = "INSERT IGNORE INTO `".DB_PREFIX."_pkg2file` SET
                                                    `packageId` = '".$DB->real_escape_string($_packID)."',
                                                    `fileId` = '".$DB->real_escape_string($hash)."'";

                                    // if this is triggered often, make sure the DB col length is also increased.
                                    if(strlen($path) > 200) Helper::sysLog('[WARNING] File path longer than 200 : '.Helper::cleanForLog($queryFile));
                                    if(QUERY_DEBUG) Helper::sysLog('[QUERY] File insert: '.Helper::cleanForLog($queryFile));
                                    if(QUERY_DEBUG) Helper::sysLog('[QUERY] File _pkg2file insert: '.Helper::cleanForLog($queryPgk2File));
                                    try {
                                        $DB->query($queryFile);
                                        $DB->query($queryPgk2File);
                                    } catch (Exception $e) {
                                        $DB->rollback();
                                        Helper::sysLog("[ERROR] File insert mysql catch: ".$e->getMessage());
                                        exit();
                                    }
                                }
                            }
                        break;
                    }
                }

                try {
                    $DB->commit();

                    // track what is updated
                    $_upId[$_packID] = $_packID;
                    $_upId[$_catID] = $_catID;
                } catch (Exception $e) {
                    $DB->rollback();
                    Helper::sysLog("[ERROR] Package commit mysql catch: ".$e->getMessage());
                    exit();
                }

                unset($_cat);
                unset($_pack);
                unset($_catID);
                unset($_packID);
                unset($_packXML);
                unset($_packageName);

                $xmlReader->next("category");
            }
        }
    }
    $xmlReader->close();

    // could be already moved due an error
    if(file_exists($fileToWorkWith)) {
        Helper::sysLog("[INFO] Imported file: ".$fileToWorkWith);
        unlink($fileToWorkWith);
    }
}

$_controlFile = PATH_CACHE.'/purgecontrol';
$_purge = false;
$_toWrite = 1;
if(file_exists($_controlFile)) {
    $_controlContent = file_get_contents($_controlFile);
    $_controlContent = trim($_controlContent);
    $_controlCounter = (int)$_controlContent;
    if($_controlCounter > 10) {
        $_purge = true;
    } else {
        $_controlCounter++;
        $_toWrite = $_controlCounter;
    }
}
file_put_contents($_controlFile, $_toWrite);

// file amount is already checked above. Avoids cleaning the cache if nothing is updated
// first clear all non id cache files
$cacheFiles = glob(PATH_CACHE.'/_*');
if(!empty($cacheFiles) && $_purge) {
    foreach($cacheFiles as $cf) {
        unlink($cf);
    }
    Helper::sysLog('[INFO] Importer purged non id files '.count($cacheFiles).' files');

    // call stats page to create new cache entry
    $call = Helper::curlCall("https://www.portagefilelist.de/index.php?p=stats");
    if($call['status'] !== false) {
        Helper::sysLog('[INFO] Importer http call '.Helper::cleanForLog($call['status']));
    } else {
        Helper::sysLog('[ERROR] Importer http call failed '.Helper::cleanForLog($call['message']));
    }
}
// now the id specific files
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
    Helper::sysLog('[INFO] Importer purged id '.count($toDelete).' files');
}

Helper::sysLog('[INFO] Importer imported '.$_fileCounter.' files');
Helper::sysLog('[INFO] Importer ended.');
