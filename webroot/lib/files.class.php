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
     * The available sort columns.
     * Used in query and sort options in FE
     *
     * @var array|array[]
     */
	private array $_sortOptions = array(
        'default' => array('col' => 'p.name', 'displayText' => 'Package (default)'),
        'arch' => array('col' => 'p.arch', 'displayText' => 'Arch'),
        'category' => array('col' => 'c.name', 'displayText' => 'Category'),
        'name' => array('col' => 'f.name', 'displayText' => 'Name'),
        'path' => array('col' => 'f.path', 'displayText' => 'Path'),
        'packageVersion' => array('col' => 'p.version', 'displayText' => 'Version')
	);

    /**
     * The available sort unique columns.
     * Used in query and sort options in FE
     *
     * @var array|array[]
     */
    private array $_sortOptionsUnique = array(
        'default' => array('col' => 'p.name', 'displayText' => 'Package (default)'),
        'arch' => array('col' => 'p.arch', 'displayText' => 'Arch'),
        'category' => array('col' => 'c.name', 'displayText' => 'Category'),
        'packageVersion' => array('col' => 'p.version', 'displayText' => 'Version')
    );

    /**
     * The used sortOptions since the unique option does use
     * different columns in the query
     *
     * @var array
     */
    private array $_so2use = array();

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
	 *  'sortDirection' => $_sortDirection,
     *  'unique' => true|false
	 * );
	 *
	 * @param array $options
	 */
	public function setQueryOptions(array $options): void {

		if(!isset($options['limit'])) $options['limit'] = 20;
		if(!isset($options['offset'])) $options['offset'] = false;

        $this->_so2use = $this->_sortOptions;
        if(isset($options['unique']) && $options['unique'] === true) {
            $this->_so2use = $this->_sortOptionsUnique;
        }
        if(isset($options['sort']) && isset($this->_so2use[$options['sort']])) {
            $options['sort'] = $this->_so2use[$options['sort']]['col'];
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
	    return $this->_so2use;
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
	 * Make the result DISTINCT by packageName with unique in queryOptions
	 *
	 * @return array
	 */
	public function getFiles() : array {
		$ret = array();

		// split since part of it is used later
		$querySelect = "f.hash AS hash,
						f.name AS name,
						f.path AS path,
						f.package_id AS packageId,
						c.name AS categoryName,
						p.name AS packageName,
						p.arch AS packageArch,
						p.version AS packageVersion,
						p.category_id AS categoryId";
		if ($this->_queryOptions['unique']) {
			$querySelect = "DISTINCT p.name AS packageName,
							f.package_id AS packageId,
							p.hash,
							p.arch AS packageArch,
							p.version AS packageVersion,
							c.hash AS categoryId,
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
			$queryOrder .= " ".$this->_so2use['default']['col'];
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
				if ($this->_queryOptions['unique']) {
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
							p.name AS packageName,
							p.hash,
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
		$options['sort'] = 'default';
		$options['sortDirection'] = '';
		$this->setQueryOptions($options);
	}
}
