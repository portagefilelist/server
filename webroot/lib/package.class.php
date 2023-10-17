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

class Package {
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
		if(!isset($options['groupby'])) $options['groupby'] = '';

		$this->_queryOptions = $options;
	}


	/**
	 * Load a package by given hash
	 *
	 * @param string $hash
	 * @return array
	 */
	public function getPackage(string $hash): array {
		$ret = array();

		if(!empty($hash)) {
			$queryStr = "SELECT p.hash, p.name, p.version, p.arch, p.category_id,
								p.importcount,
								pu.useword AS packageUse,
								c.name AS categoryName
							FROM `".DB_PREFIX."_package` AS p
							LEFT JOIN `".DB_PREFIX."_package_use` AS pu ON p.hash = pu.package_id
							LEFT JOIN `".DB_PREFIX."_category` AS c ON p.category_id = c.hash
							WHERE p.hash = '".$this->_DB->real_escape_string($hash)."'";
			try {
				$query = $this->_DB->query($queryStr);

				if($query !== false && $query->num_rows > 0) {
					$ret = $query->fetch_assoc();
					$ret['usewords'] = $this->_useflags($hash);
					$ret['otherVersions'] = $this->_otherVersionsForPackage($ret['name'], $hash, $ret['category_id']);
				}
			}
			catch (Exception $e) {
				Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
			}
		}

		return $ret;
	}

	/**
	 * Return the package files by given package hash
	 *
	 * @param string $hash
	 * @return array
	 */
	public function getPackageFiles(string $hash): array {
		$ret = array();

		// split since part of it is used later
		$querySelect = "f.hash, f.name, f.path, f.package_id";
		$queryFrom = " FROM `".DB_PREFIX."_file` AS f";

		$queryWhere = " WHERE f.package_id = '".$this->_DB->real_escape_string($hash)."'";

        if(!empty($this->_searchValue)) {
            if(str_contains($this->_searchValue, '/')) {
                $queryWhere .= " AND f.path";
            } else {
                $queryWhere .= " AND f.name";
            }

            if($this->_wildcardsearch) {
                $queryWhere .= " LIKE '".$this->_DB->real_escape_string($this->_searchValue)."'";
            } else {
                $queryWhere .= " = '".$this->_DB->real_escape_string($this->_searchValue)."'";
            }
        }

		$queryOrder = " ORDER BY";
		if (!empty($this->_queryOptions['sort'])) {
			$queryOrder .= ' '.$this->_queryOptions['sort'].' ';
		}
		else {
			$queryOrder .= " f.name";
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

		$queryStr = "SELECT ".$querySelect.$queryFrom.$queryWhere.$queryOrder.$queryLimit;
		if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStr));

		try {
			$query = $this->_DB->query($queryStr);

			if($query !== false && $query->num_rows > 0) {
				while(($result = $query->fetch_assoc()) != false) {
					$ret['results'][$result['hash']] = $result;
				}

				$queryStrCount = "SELECT COUNT(f.hash) AS amount ".$queryFrom.$queryWhere;
				if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStrCount));

				$query = $this->_DB->query($queryStrCount);
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
	 * Get the usewords for given package hash
	 *
	 * @param string $pid Package id
	 * @return array()
	 */
	private function _useflags(string $pid):array {
		$ret = array();

		if(!empty($pid)) {
			$queryStr = "SELECT pu.useword
							FROM `".DB_PREFIX."_package_use` AS pu
							WHERE pu.package_id = '".$this->_DB->real_escape_string($pid)."'";
			try {
				$query = $this->_DB->query($queryStr);

				if($query !== false && $query->num_rows > 0) {
					while(($result = $query->fetch_assoc()) != false) {
						$ret[] = $result['useword'];
					}
				}
			}
			catch (Exception $e) {
				Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
			}
		}

		return $ret;
	}

	/**
	 * Search for packages other versions
	 * Ignores self
	 *
	 * @param string $name Package name
	 * @param string $hash Package id
	 * @param string $catId Category id
	 *
	 * @return array $ret
	 */
	private function _otherVersionsForPackage(string $name, string $hash, string $catId): array {
		$ret = array();

		if(!empty($name) && !empty($hash) && !empty($catId)) {
			$queryStr = "SELECT p.hash, p.name, p.version, p.arch, p.category_id,
								c.name AS categoryName
							FROM `".DB_PREFIX."_package` AS p
							LEFT JOIN `".DB_PREFIX."_category` AS c ON p.category_id = c.hash
							WHERE p.name = '".$this->_DB->real_escape_string($name)."'
								AND p.hash <> '".$this->_DB->real_escape_string($hash)."'
								AND p.category_id = '".$this->_DB->real_escape_string($catId)."'";
			if(QUERY_DEBUG) Helper::sysLog("[QUERY] ".__METHOD__." query: ".Helper::cleanForLog($queryStr));
			try {
				$query = $this->_DB->query($queryStr);
				if($query !== false && $query->num_rows > 0) {
					while(($result = $query->fetch_assoc()) != false) {
						$ret[$result['hash']] = $result;
					}
				}
			}
			catch (Exception $e) {
				Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
			}
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
