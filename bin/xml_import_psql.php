#!/usr/bin/env php
<?php

require_once('config.php');

if ($_SERVER['argc'] != 2) {
	echo 'no file specified' . PHP_EOL;
	exit(1);
}

# the target file
$xmlfile = $_SERVER['argv'][1];

# preflight check
$dom = new DOMDocument();
$dom->load($xmlfile);
if (!$dom->schemaValidate('/www/upload.portagefilelist.de/htdocs/schema/collect.xsd')) {
	echo 'Invalid xml: "' . $xmlfile . '"' . PHP_EOL;
	exit(1);
}
unset($dom);


$importer = new PFL_Importer();

# init the xml parser
$parser = xml_parser_create_ns(null, '!'); 
xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
xml_set_element_handler($parser, array($importer, 'onElementStarted'), array($importer, 'onElementEnded'));
xml_set_default_handler($parser, array($importer, 'onData'));

# open xml file
$fh = fopen($xmlfile, 'r');
if ($fh === false) {
	echo 'Failed to open ' . $xmlfile . PHP_EOL;

}

# parse xmlfile
while (!feof($fh)) {
	$line = fgets($fh);
	xml_parse($parser, $line);
}

# close xml file
fclose($fh);

# close xml_parser
xml_parser_free($parser);

exit(0);

class PFL_Importer {
	// specific tag infos
	private $categoryid = null;
	private $packageid = null;
	private $files = null;
	private $file = null;
	private $fileids = null;
	private $archid = null;

	// data array
	private $data = array();

	// current path in xml
	private $xpath = '/';

	// postgres connection
	private $dbh = null;

	private function log($text, $ident = true) {
		if (false) {
			if ($ident) {
				echo str_pad("", substr_count($this->xpath, '/') - 2, "\t");
			}
			echo $text . PHP_EOL;
		}
	}

	private function error($text) {
		echo $text . PHP_EOL;
		exit(2);
	}

	private function initCategoryInfo() {
		$this->categoryid = null;
		$this->initPackageInfo();
	}

	private function initPackageInfo() {
		$this->packageid = null;
		$this->useflagids = array();
		$this->archid = null;
		$this->initFilesInfo();
	}

	private function initFilesInfo() {
		$this->files = array(
			'dir' => array(),
			'obj' => array(),
			'sym' => array()
		);
		$this->fileids = array();
		$this->initFileInfo();
	}

	private function initFileInfo() {
		$this->file = array(
			'type' => null
		);
	}

	private function pushXPath($tagname) {
		$this->xpath .= $tagname . '/';
	}

	private function popXPath($tagname) {
		if (substr($this->xpath, strlen($this->xpath) - strlen($tagname) - 1) != $tagname . '/') {
			$this->error('Cannot pop "' . $tagname . '" from XPath "' . $this->xpath . '"');
		}

		$this->xpath = substr($this->xpath, 0, strlen($this->xpath) - strlen($tagname) - 1);
	}

	private function START_PFL_($attributes) {
		$this->log('Starting PFL Import');
		$this->dbh = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASS);
		if (!$this->dbh) {
			$this->error('DB connect failed');
		}
	}

	private function END_PFL_() {
		pg_close($this->dbh);
	}

	private function START_PFL_CATEGORY_($attributes) {
		$this->initCategoryInfo();
		$category = $attributes['NAME'];
		$this->log($category);

		// sql
		$this->sql('INSERT INTO dir (name) VALUES (' . $this->sqlStr($category) . ') ON CONFLICT (name) DO NOTHING');
		$this->categoryid = $this->sqlSingleField('SELECT dirid FROM dir WHERE name = ' . $this->sqlStr($category));
	}

	private function START_PFL_CATEGORY_PACKAGE_($attributes) {
		$this->initPackageInfo();

		$package = array();
		$package['name'] = $attributes['NAME'];
		$package['version'] = $attributes['VERSION'];
		if (isset($attributes['ARCH'])) {
			$package['arch'] = $attributes['ARCH'];
		} else {
			$package['arch'] = '';
		}
		$package['timestamp'] = $attributes['TIMESTAMP'];

		$this->log($package['name'] . '-' . $package['version']);

		// sql
		$this->sql('BEGIN');
		$this->sql('INSERT INTO pkg (fk_dirid, name, version, compiled) VALUES (' . $this->categoryid . ', ' . $this->sqlStr($package['name']) . ', ' . $this->sqlStr($package['version']) . ', TO_TIMESTAMP(' . $this->sqlEscapeString($attributes['TIMESTAMP']) . ')) ON CONFLICT (fk_dirid, name, version) DO NOTHING');
		$this->packageid = $this->sqlSingleField('SELECT pkgid FROM pkg WHERE pkg.name = ' . $this->sqlStr($package['name']) . ' AND pkg.version = ' . $this->sqlStr($package['version']) . ' AND pkg.fk_dirid = ' . $this->categoryid);
		
		$this->sql('INSERT INTO arch (name) VALUES (' . $this->sqlStr($package['arch']) . ') ON CONFLICT (name) DO NOTHING');
		$this->archid = $this->sqlSingleField('SELECT archid FROM arch WHERE name = ' . $this->sqlStr($package['arch']));
	}

	private function END_PFL_CATEGORY_PACKAGE_() {
		$this->sql('COMMIT');
	}

	private function START_PFL_CATEGORY_PACKAGE_FILES_($attributes) {
		$this->initFilesInfo();
	}

	private function END_PFL_CATEGORY_PACKAGE_FILES_() {
		foreach (array('obj', 'sym') as $type) {
			foreach ($this->files[$type] as $file) {
				$dir = dirname($file);
				$filename = basename($file);
				
				$this->sql('INSERT INTO file (fk_pkgid, name, path, file, misc) VALUES (' . $this->packageid . ', ' . $this->sqlStr($filename) . ', ' . $this->sqlStr($dir) . ', ' . $this->sqlStr($file) . ', \'' . $type . '\') ON CONFLICT (fk_pkgid, name, path) DO NOTHING');
				$this->fileids[] = $this->sqlSingleField('SELECT fileid FROM file WHERE fk_pkgid = ' . $this->packageid . ' AND path = ' . $this->sqlStr($dir) . ' AND name = ' . $this->sqlStr($filename));
			}
		}
		
		foreach ($this->fileids as $fileid) {
			$this->sql('INSERT INTO file2arch (fk_fileid, fk_archid) VALUES (' . $fileid . ', ' . $this->archid . ') ON CONFLICT (fk_fileid, fk_archid) DO NOTHING');
		}
	}

	private function START_PFL_CATEGORY_PACKAGE_FILES_FILE_($attributes) {
		$this->initFileInfo();
		$this->file['type'] = $attributes['TYPE'];
	}

	private function END_PFL_CATEGORY_PACKAGE_FILES_FILE_() {
		$this->files[$this->file['type']][] = $this->data[$this->xpath];
	}

	private function END_PFL_CATEGORY_PACKAGE_USES_USE_() {
		$this->sql('INSERT INTO useflag (name) VALUES (' . $this->sqlStr($this->data[$this->xpath]) . ') ON CONFLICT (name) DO NOTHING');
		$useflagid = $this->sqlSingleField('SELECT useflagid FROM useflag WHERE name = ' . $this->sqlStr($this->data[$this->xpath]));
		
		foreach ($this->fileids as $fileid) {
			$this->sql('INSERT INTO file2useflag (fk_fileid, fk_useflagid) VALUES (' . $fileid . ', ' . $useflagid . ') ON CONFLICT (fk_fileid, fk_useflagid) DO NOTHING');
		}
	}

	public function onData($parser, $data) {
		switch ($this->xpath) {
			case '/PFL/CATEGORY/PACKAGE/FILES/FILE/':
			case '/PFL/CATEGORY/PACKAGE/USES/USE/':
				$this->data[$this->xpath] = trim($data);
				break;
		}
	}

	public function onElementStarted($parser, $name, $attributes) {
		list($ns, $tag) = explode('!', $name);

		// check namespace - is this needed? dom->schemaValidate should had already checked that!
		if ($ns != 'HTTP://WWW.PORTAGEFILELIST.DE/XSD/COLLECT') {
			$this->error('Invalid namespace: "' . $ns . '"');
		}

		// create current xpath
		$this->pushXPath($tag);

		// do your job
		$method = 'START' . str_replace('/', '_', $this->xpath);
		if (method_exists($this, $method)) {
			$this->$method($attributes);
		} else {
			// ignore me
		}
	}

	public function onElementEnded($parser, $name) {
		list($ns, $tag) = explode('!', $name);

		// check namespace - is this needed? dom->schemaValidate should had already checked that!
		if ($ns != 'HTTP://WWW.PORTAGEFILELIST.DE/XSD/COLLECT') {
			$this->error('Invalid namespace: "' . $ns . '"');
		}

		// do your job
		$method = 'END' . str_replace('/', '_', $this->xpath);
		if (method_exists($this, $method)) {
			$this->$method();
		} else {
			// ignore me
		}

		//
		$this->popXPath($tag);
	}
	
	private function sqlSingleField($sql) {
		$data = pg_fetch_array($this->sql($sql));
		if ($data) {
			return $data[0];
		} else {
			return null;
		}
	}

	private function sql($sql) {
		$ret = pg_query($this->dbh, $sql);
		if (!$ret) {
			$this->log($sql, false);
			$this->error(pg_last_error($this->dbh));
		}
		return $ret;
	}
	
	private function sqlStr($str) {
		return '\'' . $this->sqlEscapeString($str) . '\'';
	}

	private function sqlStrOrNull($str) {
		if ($str == '') {
			return 'NULL';
		} else {
			return '\'' . $this->sqlEscapeString($str) . '\'';
		}
	}
	
	private function sqlEscapeString($str) {
		return pg_escape_string($this->dbh, $str);
	}
}


?>
