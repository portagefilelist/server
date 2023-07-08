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

require_once 'lib/category.class.php';
$Category = new Category($DB);

# pagination
$TemplateData['pagination'] = array('pages' => 0, 'currentGetParameters' => array('p' => 'category'));

$_curPage = 1;
if(isset($_GET['page']) && !empty($_GET['page'])) {
	$_curPage = trim($_GET['page']);
	$_curPage = Helper::validate($_curPage,'digit') ? $_curPage : 1;
}

$_sort = '';
if(isset($_GET['s']) && !empty($_GET['s'])) {
	$_sort = trim($_GET['s']);
	$_sort = Helper::validate($_sort,'nospace') ? $_sort : '';
}

$_sortDirection = '';
if(isset($_GET['sd']) && !empty($_GET['sd'])) {
	$_sortDirection = trim($_GET['sd']);
	$_sortDirection = Helper::validate($_sortDirection,'nospace') ? $_sortDirection : '';
}

$queryOptions = array(
	'limit' => RESULTS_PER_PAGE,
	'offset' => (RESULTS_PER_PAGE * ($_curPage-1)),
	'sort' => $_sort,
	'sortDirection' => $_sortDirection
);
## pagination end

$_id = '';
if(isset($_GET['id']) && !empty($_GET['id'])) {
	$_id = trim($_GET['id']);
	$_id = Helper::validate($_id,'nospace') ? $_id : '';
}

$TemplateData['pageTitle'] = 'Category';
$TemplateData['category'] = array();
$TemplateData['packages'] = array();

if(!empty($_id)) {
	$Category->setQueryOptions($queryOptions);
	$category = $Category->getCategory($_id);
	if(!empty($category)) {
		$TemplateData['category'] = $category;
		$TemplateData['packages'] = $Category->getPackages($_id);

		$TemplateData['pageTitle'] = $category['name'];
		$TemplateData['pagination']['currentGetParameters']['id'] = $_id;
	} else {
		$messageData['status'] = "error";
		$messageData['message'] = "Invalid category id";
	}
}

## pagination
if(!empty($TemplateData['packages']['amount'])) {
	$TemplateData['pagination']['pages'] = (int)ceil($TemplateData['packages']['amount'] / RESULTS_PER_PAGE);
	$TemplateData['pagination']['curPage'] = $_curPage;

	$TemplateData['pagination']['currentGetParameters']['page'] = $_curPage;
	$TemplateData['pagination']['currentGetParameters']['s'] = $_sort;
	$TemplateData['pagination']['currentGetParameters']['sd'] = $_sortDirection;
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
