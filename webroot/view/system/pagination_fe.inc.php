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

if(!empty($TemplateData['pagination']) && $TemplateData['pagination']['pages'] > 1) {
	echo '<ul class="pagination" id="pagination">';
	if($TemplateData['pagination']['curPage'] > 1) {
		echo '<li class="page-item"><a href="index.php?'.Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'],array('page'=>($TemplateData['pagination']['curPage']-1))).'#pagination">&lt;</a></li>';
	} else {
		echo '<li class="page-item disabled"><a href="">&lt;</a></li>';
	}
	$ellipsisShown = 0;

	for($i=1;$i<=$TemplateData['pagination']['pages'];$i++) {
		$active = '';
		if($i == $TemplateData['pagination']['curPage']) $active = 'active';

		if(in_array($i,$TemplateData['pagination']['visibleRange'])) {
			echo '<li class="page-item '.$active.'"><a href="index.php?'.Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'],array('page'=>$i)).'#pagination" title="Goto page '.$i.'">'.$i.'</a></li>';
		}
		else {
			if($i < $TemplateData['pagination']['currentRangeStart'] && $ellipsisShown == 0) {
				echo '<li class="page-item disabled"><span>&hellip;</span></li>';
				$ellipsisShown = 1;
			}
			if($i > $TemplateData['pagination']['currentRangeEnd'] && ($ellipsisShown == 0 || $ellipsisShown == 1)) {
				echo '<li class="page-item disabled"><span>&hellip;</span></li>';
				$ellipsisShown = 2;
			}
		}
	}

	if($TemplateData['pagination']['curPage'] < $TemplateData['pagination']['pages']) {
		echo '<li class="page-item"><a href="index.php?'.Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'],array('page'=>($TemplateData['pagination']['curPage']+1))).'#pagination">&gt;</a></li>';
	} else {
		echo '<li class="page-item disabled"><a href="">&gt;</a></li>';
	}
	echo '</ul>';
}
?>
