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
 * pre 2023 https://github.com/tuxmainy
 * 2023 https://www.bananas-playground.net/projekt/portagefilelist/
 */

class Category {
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
     * The available sort columns.
     * Used in query and sort options in FE
     *
     * @var array|array[]
     */
    private array $_sortOptions = array(
        'default' => array('col' => 'p.name', 'displayText' => 'Name (default)'),
        'arch' => array('col' => 'p.arch', 'displayText' => 'Arch'),
        'version' => array('col' => 'p.version', 'displayText' => 'Version')
    );

    /**
     * Category constructor.
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
     * Load a category by given hash
     *
     * @param string $hash
     * @return array
     */
    public function getCategory(string $hash): array {
        $ret = array();

        if(!empty($hash)) {
            $queryStr = "SELECT c.hash, c.name 
                            FROM `".DB_PREFIX."_category` AS c
                            WHERE c.hash = '".$this->_DB->real_escape_string($hash)."'";
            try {
                $query = $this->_DB->query($queryStr);

                if($query !== false && $query->num_rows > 0) {
                    $ret = $query->fetch_assoc();
                }
            }
            catch (Exception $e) {
                Helper::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        return $ret;
    }

    /**
     * Return the category packages by given category hash
     *
     * @param string $hash
     * @return array
     */
    public function getPackages(string $hash): array {
        $ret = array();

        // split since part of it is used later
        $querySelect = "p.hash, p.name, p.version, p.arch, 
                        c2p.categoryId";
        $queryFrom = " FROM `".DB_PREFIX."_cat2pkg` AS c2p";

        $queryJoin = " LEFT JOIN `".DB_PREFIX."_package` AS p on p.hash = c2p.packageId
                       LEFT JOIN `".DB_PREFIX."_category` AS c ON c.hash = c2p.categoryId";

        $queryWhere = " WHERE c2p.categoryId = '".$this->_DB->real_escape_string($hash)."'";

        $queryOrder = " ORDER BY";
        if (!empty($this->_queryOptions['sort'])) {
            $queryOrder .= ' '.$this->_queryOptions['sort'].' ';
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

                $queryStrCount = "SELECT COUNT(p.hash) AS amount ".$queryFrom.$queryJoin.$queryWhere;
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
