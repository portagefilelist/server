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

class Files {
	/**
	 * the database object
	 *
	 * @var mysqli
	 */
	private mysqli $_DB;

	/**
	 * Options for db queries
	 *  'limit' => int,
	 *  'offset' => int,
	 *  'orderby' => string,
	 *  'sortDirection' => ASC|DESC
	 *
	 * @var array
	 */
	private array $_queryOptions;

	/**
	 * @var string $_searchValue
	 */
	private string $_searchValue;

	/**
	 * @var bool $_wildcardsearch
	 */
	private bool $_wildcardsearch = false;

	/**
	 * Files constructor.
	 *
	 * @param mysqli $databaseConnectionObject
	 */
	public function __construct(mysqli $databaseConnectionObject) {
		$this->_DB = $databaseConnectionObject;
		$this->_setDefaults();
	}

	/**
	 * Set the following options which can be used in DB queries
	 * array(
	 *  'limit' => RESULTS_PER_PAGE,
	 *  'offset' => (RESULTS_PER_PAGE * ($_curPage-1)),
	 *  'orderby' => $_sort,
	 *  'sortDirection' => $_sortDirection
	 * );
	 *
	 * @param array $options
	 */
	public function setQueryOptions(array $options): void {

		if(!isset($options['limit'])) $options['limit'] = 20;
		if(!isset($options['offset'])) $options['offset'] = false;
		if(!isset($options['sort'])) $options['sort'] = false;
		if(!isset($options['sortDirection'])) $options['sortDirection'] = false;

		$this->_queryOptions = $options;
	}

	/**
	 * Prepare and set the searchvalue.
	 * Check for wildcardsearch and make it safe
	 *
	 * @param string $searchValue
	 * @return bool
	 */
	public function prepareSearchValue(string $searchValue): bool {
		Helper::sysLog("[INFO] ".__METHOD__." wanted searchvalue: ".Helper::cleanForLog($searchValue));

		if(str_contains($searchValue,'*')) {
			$this->_wildcardsearch = true;
			$searchValue = preg_replace('/\*{1,}/', '%', $searchValue);

			if(strlen($searchValue) < 3) {
				return false;
			}

			if(strlen($searchValue) === 3) {
				if(substr_count($searchValue, '%') > 1) return false;
			}
		}

		if(strlen($searchValue) < 2) {
			return false;
		}

		$this->_searchValue = $searchValue;

		return true;
	}

	/**
	 * search within files from prepareSearchValue()
	 * Make the result DISTINCT by packageName with $_uniquePackages
	 *
	 * @param bool $_uniquePackages
	 * @return array
	 */
	public function getFiles(bool $_uniquePackages) : array {
		$ret = array();

		// split since part of it is used later
		$querySelect = "f.hash AS hash,
						f.name AS name,
						f.path AS path,
						f.package_id AS package_id,
						c.name AS categoryName,
						p.name AS packageName,
						p.arch AS packageArch,
						p.version AS packageVersion,
						p.category_id AS category_id";
		if ($_uniquePackages) {
			$querySelect = "DISTINCT p.name AS packageName,
							f.package_id AS package_id,
							p.hash,
							p.arch AS packageArch,
							p.version AS packageVersion,
							c.hash AS category_id,
							c.name AS categoryName";
		}

		$queryFrom = " FROM `".DB_PREFIX."_file` AS f";

		$queryJoin = " LEFT JOIN `".DB_PREFIX."_package` AS p ON f.package_id = p.hash";
		$queryJoin .= " LEFT JOIN `".DB_PREFIX."_category` AS c ON p.category_id = c.hash";

		if(str_contains($this->_searchValue, '/')) {
			$queryWhere = " WHERE f.path";
		} else {
			$queryWhere = " WHERE f.name";
		}

		if($this->_wildcardsearch) {
			$queryWhere .= " LIKE '".$this->_DB->real_escape_string($this->_searchValue)."'";
		} else {
			$queryWhere .= " = '".$this->_DB->real_escape_string($this->_searchValue)."'";
		}

		$queryOrder = " ORDER BY";
		if (!empty($this->_queryOptions['sort'])) {
			$queryOrder .= ' '.$this->_queryOptions['sort'];
		}
		else {
			$queryOrder .= " p.name";
		}

		if (!empty($this->_queryOptions['sortDirection'])) {
			$queryOrder .= ' '.$this->_queryOptions['sortDirection'];
		}
		else {
			$queryOrder .= " ASC";
		}

		$queryLimit = '';
		if(!empty($this->_queryOptions['limit'])) {
			$queryLimit .= " LIMIT ".$this->_queryOptions['limit'];
			# offset can be 0
			if($this->_queryOptions['offset'] !== false) {
				$queryLimit .= " OFFSET ".$this->_queryOptions['offset'];
			}
		}

		$queryStr = "SELECT ".$querySelect.$queryFrom.$queryJoin.$queryWhere.$queryOrder.$queryLimit;
		if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStr));

		try {
			$query = $this->_DB->query($queryStr);

			if($query !== false && $query->num_rows > 0) {
				while(($result = $query->fetch_assoc()) != false) {
					$ret['results'][$result['hash']] = $result;
				}

				$queryStrCount = "SELECT COUNT(*) AS amount ".$queryFrom.$queryJoin.$queryWhere;
				if ($_uniquePackages) {
					$queryStrCount = "SELECT COUNT(DISTINCT p.name) AS amount ".$queryFrom.$queryJoin.$queryWhere;
				}

				if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStrCount));
				$query = $this->_DB->query($queryStrCount);
				$result = $query->fetch_assoc();
				$ret['amount'] = $result['amount'];

				$statsQuery = "INSERT INTO `".DB_PREFIX."_statslog` SET
								`type` = 'filesearch',
								`value` = '".$this->_DB->real_escape_string($this->_searchValue)."'";
				if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($statsQuery));
				$this->_DB->query($statsQuery);
			}
		}
		catch (Exception $e) {
			Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
		}

		return $ret;
	}

	/**
	 * Return some general stats about files table
	 *
	 * @return array('latest' => array(), 'amount' => '')
	 */
	public function stats():array {
		$ret = array(
			'latest' => array(),
			'amount' => ''
		);

		// latest updated
		$queryStr = "SELECT f.name,
							f.package_id AS package_id,
							p.name AS packageName,
							p.hash,
							c.hash AS category_id,
							c.name AS categoryName
					FROM `".DB_PREFIX."_file` AS f
					LEFT JOIN `".DB_PREFIX."_package` AS p ON f.package_id = p.hash
					LEFT JOIN `".DB_PREFIX."_category` AS c ON p.category_id = c.hash
					ORDER BY f.lastmodified DESC
					LIMIT 10";
		if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStr));

		try {
			$query = $this->_DB->query($queryStr);

			if($query !== false && $query->num_rows > 0) {
				while(($result = $query->fetch_assoc()) != false) {
					$ret['latest'][] = $result;
				}
			}
		}
		catch (Exception $e) {
			Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
		}

		// Amount of files
		$queryStr = "SELECT COUNT(f.hash) AS amount
					FROM `".DB_PREFIX."_file` AS f
					WHERE f.hash IS NOT NULL";
		if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStr));

		try {
			$query = $this->_DB->query($queryStr);

			if($query !== false && $query->num_rows > 0) {
				$result = $query->fetch_assoc();
				$ret['amount'] = $result['amount'];
			}
		}
		catch (Exception $e) {
			Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
		}


		return $ret;
	}

	/**
	 * statslog entries for filesearch type and more then 2 entries
	 * Sorted and reduced to the first entry for each amount
	 *
	 * @return array
	 */
	public function topSearch(): array {
		$ret = array();

		$queryStr = "SELECT COUNT(sl.value) AS amount, sl.value
					FROM `".DB_PREFIX."_statslog` AS sl
					WHERE sl.type = 'filesearch'
					GROUP BY sl.value
					HAVING amount > 2
					ORDER BY amount DESC
					LIMIT 10";
		if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStr));

		try {
			$query = $this->_DB->query($queryStr);

			if($query !== false && $query->num_rows > 0) {
				while(($row = $query->fetch_assoc()) != false) {
					if(!isset($ret[$row['amount']])) {
						$ret[$row['amount']] = str_replace("%", "*", $row['value']);
					}
				}
			}
		}
		catch (Exception $e) {
			Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
		}

		return $ret;
	}

	/**
	 * set some defaults by init of the class
	 *
	 * @return void
	 */
	private function _setDefaults(): void {
		// default query options
		$options['limit'] = 50;
		$options['offset'] = false;
		$options['sort'] = false;
		$options['sortDirection'] = false;
		$this->setQueryOptions($options);
	}
}
