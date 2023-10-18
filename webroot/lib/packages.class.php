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

class Packages {
	/**
	 * the database object
	 *
	 * @var mysqli
	 */
	private $_DB;

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
     * The available sort columns.
     * Used in query and sort options in FE
     *
     * @var array|array[]
     */
    private array $_sortOptions = array(
        'default' => array('col' => 'p.name', 'displayText' => 'Name (default)'),
        'arch' => array('col' => 'p.arch', 'displayText' => 'Arch'),
        'category' => array('col' => 'c.name', 'displayText' => 'Category'),
        'version' => array('col' => 'p.version', 'displayText' => 'Version')
    );

	/**
	 * Packages constructor.
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

        if(isset($options['sort']) && isset($this->_sortOptions[$options['sort']])) {
            $options['sort'] = $this->_sortOptions[$options['sort']]['col'];
        } else {
            $options['sort'] = '';
        }

        if(isset($options['sortDirection'])) {
            $options['sortDirection'] = match ($options['sortDirection']) {
                'desc' => "DESC",
                default => "ASC",
            };
        } else {
            $options['sortDirection'] = '';
        }

		$this->_queryOptions = $options;
	}

    /**
     * Return the available sort options and the active used one
     *
     * @return array|array[]
     */
    public function getSortOptions(): array {
        return $this->_sortOptions;
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
	 * search packages by given searchValue
	 *
	 * @return array
	 */
	public function getPackages() : array {
		$ret = array();

		// split since part of it is used later
		$querySelect = "p.hash,
						p.name,
						p.version,
						p.arch,
						p.category_id AS category_id,
						c.name AS categoryName";

		$queryFrom = " FROM `".DB_PREFIX."_package` AS p";

		$queryJoin = " LEFT JOIN `".DB_PREFIX."_category` AS c ON p.category_id = c.hash";

		$queryWhere = " WHERE p.name";

		if($this->_wildcardsearch) {
			$queryWhere .= " LIKE '".$this->_DB->real_escape_string($this->_searchValue)."'";
		} else {
			$queryWhere .= " = '".$this->_DB->real_escape_string($this->_searchValue)."'";
		}

		$queryOrder = " ORDER BY";
		if (!empty($this->_queryOptions['sort'])) {
			$queryOrder .= ' '.$this->_queryOptions['sort'].'';
		}
		else {
			$queryOrder .= " ".$this->_sortOptions['default']['col'];
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

				if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStrCount));
				$query = $this->_DB->query($queryStrCount);
				$result = $query->fetch_assoc();
				$ret['amount'] = $result['amount'];

				$statsQuery = "INSERT INTO `".DB_PREFIX."_statslog` SET
								`type` = 'pkgsearch',
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
	 * Return some general stats about packages table
	 *
	 * @return array('latest' => array(), 'amount' => '', 'arch' => array(), 'use' => array())
	 */
	public function stats():array {
		$ret = array(
			'latest' => array(),
			'amount' => '',
			'arch' => array(),
			'use' => array()
		);

		// latest updated
		$ret['latest'] = $this->latestUpdated();

		// Amount of packages
		$queryStr = "SELECT COUNT(p.hash) AS amount
					FROM `".DB_PREFIX."_package` AS p WHERE p.hash IS NOT NULL";
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

		// arch
		$queryStr = "SELECT DISTINCT p.arch
					FROM `".DB_PREFIX."_package` AS p";
		if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStr));

		try {
			$query = $this->_DB->query($queryStr);

			if($query !== false && $query->num_rows > 0) {
				while(($result = $query->fetch_assoc()) != false) {
					if(!empty($result['arch'])) {
						$ret['arch'][] = $result['arch'];
					}
				}
			}
		}
		catch (Exception $e) {
			Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
		}


		// use
		$queryStr = "SELECT COUNT(*) AS amount, p.useword 
					FROM `".DB_PREFIX."_package_use` AS p
					GROUP BY p.useword 
					ORDER BY `amount` DESC 
					LIMIT 10";
		if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStr));

		try {
			$query = $this->_DB->query($queryStr);

			if($query !== false && $query->num_rows > 0) {
				while(($result = $query->fetch_assoc()) != false) {
					$ret['use'][] = $result;
				}
			}
		}
		catch (Exception $e) {
			Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
		}

		return $ret;
	}

	/**
	 * latest 10 updated or imported packages
	 *
	 * @return array
	 */
	public function latestUpdated(): array {
		$ret = array();

		// latest updated
		$queryStr = "SELECT p.hash,
						p.name,
						p.lastmodified,
						p.category_id AS category_id,
						c.name AS categoryName
					FROM `".DB_PREFIX."_package` AS p
					LEFT JOIN `".DB_PREFIX."_category` AS c ON p.category_id = c.hash
					ORDER BY p.lastmodified DESC
					LIMIT 10";
		if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStr));

		try {
			$query = $this->_DB->query($queryStr);

			if($query !== false && $query->num_rows > 0) {
				while(($result = $query->fetch_assoc()) != false) {
					$ret[] = $result;
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
		$options['sort'] = 'default';
		$options['sortDirection'] = '';
		$this->setQueryOptions($options);
	}
}
