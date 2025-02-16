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
 * 2023 - 2025 https://www.bananas-playground.net/projekt/portagefilelist/
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
                $messageData['statusCode'] = 404;
            }

            $TemplateData['searchInput'] = htmlspecialchars($searchValue);
            $TemplateData['pagination']['currentGetParameters']['cs'] = urlencode($searchValue);
        } else {
            $messageData['status'] = "danger";
            $messageData['message'] = "Invalid search criteria. At least two (without wildcard) chars.";
            $messageData['statusCode'] = 404;
        }
    } else {
        $messageData['status'] = "danger";
        $messageData['message'] = "Invalid search criteria.";
        $messageData['statusCode'] = 404;
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
