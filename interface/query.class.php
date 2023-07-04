<?php
class Query {

	public function file() {
		$this->filereq();
	}

	public function filecsv() {
		$header = 'Content-Type: text/plain';
		$_REQUEST['do'] = true;
		$this->filereq();
	}

	private function filereq() {
		$result = null;
		$file = '';
		$unique = true;

		if (isset($_REQUEST['do']) && isset($_REQUEST['file'])) {
			$file = $_REQUEST['file'];
			$unique = isset($_REQUEST['unique_packages']);
			$search = new Search();
			$result = $search->queryFile($file, $unique);
		}

		// handle output of $result
	}
	
	public function robotFile() {
		$ret = array();

		try {
			if (isset($_REQUEST['file'])) {
				$ret['result'] = $this->getFileDatas();
				$this->sendJSON($ret);
			} else {
				$this->sendJSONError('NO_SEARCH_CRITERIA', "No search criteria given but 'file' is required");
			}
		} catch (Exception $e) {
			$this->sendJSONError('UNKNOWN', "unexpected error: " . $e->getMessage());
		}
		
	}

	private function sendJSONError($code, $message) {
		$ret = array();
		$ret['error']['code'] = $code;
		$ret['error']['message'] = $message;

		http_response_code(400);
		$this->sendJSON($ret);
	}
	
	private function sendJSON($data) {
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');

		die(json_encode($data));
	}
	
	private function getFileDatas() {
		$file = $_REQUEST['file'];
		$unique = isset($_REQUEST['unique_packages']);
		$search = new Search();
		$datas = $search->queryFile($file, $unique);
		foreach ($datas as &$data) {
			unset($data['type']);
			
			if ($data['useflags'] === null || trim($data['useflags']) === '') {
				$data['useflags'] = array();
			} else {
				$data['useflags'] = $this->list2array($data['useflags']);
			}
			
			if ($data['archs'] === null || trim($data['archs']) === '') {
				$data['archs'] = array();
			} else {
				$data['archs'] = $this->list2array($data['archs']);
			}
		}
		
		return $datas;
	}
	
	private function list2array($list) {
		$arr = explode(',', $list);
		return array_map('trim', $arr);
	}

	public function listPackageVersions() {
		$result = null;
		$category = '';
		$package = '';

		if (isset($_REQUEST['do']) && isset($_REQUEST['category']) && isset($_REQUEST['package'])) {
			$category = $_REQUEST['category'];
			$package = $_REQUEST['package'];

			$search = new Search();
			$result = $search->queryPackageVersions($category, $package);
		}

		// handle output of result
	}
	
	public function robotListPackageVersions() {
		$ret = array();

		try {
			if (isset($_REQUEST['category']) && isset($_REQUEST['package'])) {
				$search = new Search();
				$result = $search->queryPackageVersions($_REQUEST['category'], $_REQUEST['package']);
				$ret['result'] = $result;
				$this->sendJSON($ret);
			} else {
				$this->sendJSONError('NO_SEARCH_CRITERIA', "Missing search criteria. 'category' and 'package' are required");
			}
		} catch (Exception $e) {
			$this->sendJSONError('UNKNOWN', "unexpected error: " . $e->getMessage());
		}
		
	}

	public function listPackageFiles() {
		$result = null;
		$category = '';
		$package = '';
		$version = '';

		if (isset($_REQUEST['do']) && isset($_REQUEST['category']) && isset($_REQUEST['package']) && isset($_REQUEST['version'])) {
			$category = $_REQUEST['category'];
			$package = $_REQUEST['package'];
			$version = $_REQUEST['version'];

			$search = new Search();
			$result = $search->queryPackageFiles($category, $package, $version);
		}

		// handle output of $result
	}
	
	public function robotListPackageFiles() {
		$ret = array();

		try {
			if (isset($_REQUEST['category']) && isset($_REQUEST['package']) && isset($_REQUEST['version'])) {
				$ret['result'] = $this->getListPackageFilesDatas();
				$this->sendJSON($ret);
			} else {
				$this->sendJSONError('NO_SEARCH_CRITERIA', "Missing search criteria. 'category', 'package' and 'version' are required");
			}
		} catch (Exception $e) {
			$this->sendJSONError('UNKNOWN', "unexpected error: " . $e->getMessage());
		}
	}

	private function getListPackageFilesDatas() {
		$category = $_REQUEST['category'];
		$package = $_REQUEST['package'];
		$version = $_REQUEST['version'];
		
		$search = new Search();
		$datas = $search->queryPackageFiles($category, $package, $version);
		foreach ($datas as &$data) {
			unset($data['type']);
			
			if ($data['useflags'] === null || trim($data['useflags']) === '') {
				$data['useflags'] = array();
			} else {
				$data['useflags'] = $this->list2array($data['useflags']);
			}
			
			if ($data['archs'] === null || trim($data['archs']) === '') {
				$data['archs'] = array();
			} else {
				$data['archs'] = $this->list2array($data['archs']);
			}
		}
		
		return $datas;
	}
}

?>
