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

if(!empty($TemplateData['pagination']) && $TemplateData['pagination']['pages'] > 1) { ?>

<div class="columns">
	<div class="column col-6 col-lg-12">
		<ul class="pagination">
<?php
	if($TemplateData['pagination']['curPage'] > 1) {
		echo '<li class="page-item"><a href="index.php?'.Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'],array('page'=>($TemplateData['pagination']['curPage']-1))).'#panchor">&lt;</a></li>';
	} else {
		echo '<li class="page-item disabled"><a href="">&lt;</a></li>';
	}
	$ellipsisShown = 0;

	for($i=1;$i<=$TemplateData['pagination']['pages'];$i++) {
		$active = '';
		if($i == $TemplateData['pagination']['curPage']) $active = 'active';

		if(in_array($i,$TemplateData['pagination']['visibleRange'])) {
			echo '<li class="page-item '.$active.'"><a href="index.php?'.Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'],array('page'=>$i)).'#panchor" title="Goto page '.$i.'">'.$i.'</a></li>';
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
		echo '<li class="page-item"><a href="index.php?'.Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'],array('page'=>($TemplateData['pagination']['curPage']+1))).'#panchor">&gt;</a></li>';
	} else {
		echo '<li class="page-item disabled"><a href="">&gt;</a></li>';
	}
?>
		</ul>
	</div>
	<div class="column col-6 col-lg-12 text-right">
		<div class="paginationSortCol">
			<div class="dropdown dropdown-right">
				<a class="btn dropdown-toggle" tabindex="0">Column <i class="icon icon-caret"></i></a>
				<ul class="menu text-left">
				<?php
				foreach($TemplateData['pagination']['sortOptions'] as $k=>$v) {
                    $active = '';
					if($k === $TemplateData['pagination']['currentGetParameters']['s']) $active = "active";
				?>
					<li class="menu-item"><a class="<?php echo $active; ?>" href="index.php?<?php echo Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'], array('s' => $k));  ?>#panchor"><?php echo $v['displayText']; ?></a></li>
				<?php } ?>
				</ul>
			</div>
			<div class="dropdown dropdown-right">
				<a class="btn dropdown-toggle" tabindex="0">Sort <i class="icon icon-caret"></i></a>
				<ul class="menu text-left">
					<li class="menu-item"><a class="<?php if($TemplateData['pagination']['currentGetParameters']['sd'] === "asc") echo "active"; ?>" href="index.php?<?php echo Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'], array('sd' => 'asc'));  ?>#panchor">ASC (default)</a></li>
					<li class="menu-item"><a class="<?php if($TemplateData['pagination']['currentGetParameters']['sd'] === "desc") echo "active"; ?>" href="index.php?<?php echo Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'], array('sd' => 'desc'));  ?>#panchor">DESC</a></li>
				</ul>
			</div>
			<div class="dropdown dropdown-right">
				<a class="btn dropdown-toggle" tabindex="0">Amount <i class="icon icon-caret"></i></a>
				<ul class="menu text-left">
					<li class="menu-item"><a class="<?php if($TemplateData['pagination']['currentGetParameters']['rpp'] == RESULTS_PER_PAGE) echo "active"; ?>" href="index.php?<?php echo Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'], array('rpp' => RESULTS_PER_PAGE, 'page' => '1')); ?>#panchor"><?php echo RESULTS_PER_PAGE; ?> (default)</a></li>
					<li class="menu-item"><a class="<?php if($TemplateData['pagination']['currentGetParameters']['rpp'] == RESULTS_PER_PAGE*2) echo "active"; ?>" href="index.php?<?php echo Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'], array('rpp' => RESULTS_PER_PAGE*2, 'page' => '1'));  ?>#panchor"><?php echo RESULTS_PER_PAGE*2; ?></a></li>
					<li class="menu-item"><a class="<?php if($TemplateData['pagination']['currentGetParameters']['rpp'] == RESULTS_PER_PAGE*3) echo "active"; ?>" href="index.php?<?php echo Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'], array('rpp' => RESULTS_PER_PAGE*3, 'page' => '1'));  ?>#panchor"><?php echo RESULTS_PER_PAGE*3; ?></a></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<?php
}
?>
