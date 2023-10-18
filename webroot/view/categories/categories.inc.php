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

require_once 'lib/categories.class.php';
$Categories = new Categories($DB);

# pagination
$TemplateData['pagination'] = array('pages' => 0, 'currentGetParameters' => array('p' => 'categories'));

$_curPage = 1;
if(isset($_GET['page']) && !empty($_GET['page'])) {
	$_curPage = trim($_GET['page']);
	$_curPage = Helper::validate($_curPage,'digit') ? $_curPage : 1;
}

$_sort = 'default';
if(isset($_GET['s']) && !empty($_GET['s'])) {
	$_sort = trim($_GET['s']);
	$_sort = Helper::validate($_sort,'nospace') ? $_sort : 'default';
}

$_sortDirection = '';
if(isset($_GET['sd']) && !empty($_GET['sd'])) {
	$_sortDirection = trim($_GET['sd']);
	$_sortDirection = Helper::validate($_sortDirection,'nospace') ? $_sortDirection : '';
}

$_rpp = RESULTS_PER_PAGE;
if(isset($_GET['rpp']) && !empty($_GET['rpp'])) {
    $_rpp = trim($_GET['rpp']);
    $_rpp = Helper::validate($_rpp,'digit') ? $_rpp : RESULTS_PER_PAGE;
}

$queryOptions = array(
	'limit' => $_rpp,
	'offset' => ($_rpp * ($_curPage-1)),
	'sort' => $_sort,
	'sortDirection' => $_sortDirection
);
## pagination end

$TemplateData['pageTitle'] = 'Search for categories';
$TemplateData['searchresults'] = array();
$TemplateData['searchInput'] = '';

## search
if(isset($_GET['cs'])) {
	$searchValue = trim($_GET['cs']);
	$searchValue = strtolower($searchValue);
	$searchValue = urldecode($searchValue);

	if(Helper::validate($searchValue,'nospaceP')) {
		$Categories->setQueryOptions($queryOptions);
		if($Categories->prepareSearchValue($searchValue)) {
			$TemplateData['searchresults'] = $Categories->getCategories();

			if(empty($TemplateData['searchresults'])) {
				$messageData['status'] = "warning";
				$messageData['message'] = "Nothing found for this search criteria or the data is not known yet.";
			}

			$TemplateData['searchInput'] = htmlspecialchars($searchValue);
			$TemplateData['pagination']['currentGetParameters']['cs'] = urlencode($searchValue);
		} else {
			$messageData['status'] = "error";
			$messageData['message'] = "Invalid search criteria. At least two (without wildcard) chars.";
		}
	} else {
		$messageData['status'] = "error";
		$messageData['message'] = "Invalid search criteria.";
	}
}
## search end

## pagination
if(!empty($TemplateData['searchresults']['amount'])) {
	$TemplateData['pagination']['pages'] = (int)ceil($TemplateData['searchresults']['amount'] / $_rpp);
	$TemplateData['pagination']['curPage'] = $_curPage;

	$TemplateData['pagination']['currentGetParameters']['page'] = $_curPage;
	$TemplateData['pagination']['currentGetParameters']['s'] = $_sort;
	$TemplateData['pagination']['currentGetParameters']['sd'] = $_sortDirection;
    $TemplateData['pagination']['currentGetParameters']['rpp'] = $_rpp;
    $TemplateData['pagination']['sortOptions'] = $Categories->getSortOptions();
}

if($TemplateData['pagination']['pages'] > 11) {
	# first pages
	$TemplateData['pagination']['visibleRange'] = range(1,3);
	# last pages
	foreach(range($TemplateData['pagination']['pages']-2, $TemplateData['pagination']['pages']) as $e) {
		$TemplateData['pagination']['visibleRange'][] = $e;
	}
	# pages before and after current page
	$cRange = range($TemplateData['pagination']['curPage']-1, $TemplateData['pagination']['curPage']+1);
	foreach($cRange as $e) {
		$TemplateData['pagination']['visibleRange'][] = $e;
	}
	$TemplateData['pagination']['currentRangeStart'] = array_shift($cRange);
	$TemplateData['pagination']['currentRangeEnd'] = array_pop($cRange);
}
else {
	$TemplateData['pagination']['visibleRange'] = range(1,$TemplateData['pagination']['pages']);
}
## pagination end
