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
 * 2023 - 2024 https://www.bananas-playground.net/projekt/portagefilelist/
 */

if(!empty($TemplateData['pagination']) && $TemplateData['pagination']['pages'] > 1) { ?>

<div class="uk-grid">
	<div class="uk-width-1-2@m uk-width-auto@s">
		<ul class="uk-pagination">
<?php
	if($TemplateData['pagination']['curPage'] > 1) {
		echo '<li class=""><a href="index.php?'.Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'],array('page'=>($TemplateData['pagination']['curPage']-1))).'#panchor">&lt;</a></li>';
	} else {
		echo '<li class="uk-disabled"><a href="">&lt;</a></li>';
	}
	$ellipsisShown = 0;

	for($i=1;$i<=$TemplateData['pagination']['pages'];$i++) {
		$active = '';
		if($i == $TemplateData['pagination']['curPage']) $active = 'uk-active';

		if(in_array($i,$TemplateData['pagination']['visibleRange'])) {
			echo '<li class="'.$active.'"><a href="index.php?'.Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'],array('page'=>$i)).'#panchor" title="Goto page '.$i.'">'.$i.'</a></li>';
		}
		else {
			if($i < $TemplateData['pagination']['currentRangeStart'] && $ellipsisShown == 0) {
				echo '<li class="uk-disabled"><span>&hellip;</span></li>';
				$ellipsisShown = 1;
			}
			if($i > $TemplateData['pagination']['currentRangeEnd'] && ($ellipsisShown == 0 || $ellipsisShown == 1)) {
				echo '<li class="uk-disabled"><span>&hellip;</span></li>';
				$ellipsisShown = 2;
			}
		}
	}

	if($TemplateData['pagination']['curPage'] < $TemplateData['pagination']['pages']) {
		echo '<li class=""><a href="index.php?'.Helper::createFromParameterLinkQuery($TemplateData['pagination']['currentGetParameters'],array('page'=>($TemplateData['pagination']['curPage']+1))).'#panchor">&gt;</a></li>';
	} else {
		echo '<li class="uk-disabled"><a href="">&gt;</a></li>';
	}
?>
		</ul>
	</div>
	<div class="uk-width-1-2@m uk-width-auto@s">
        <div class="uk-align-right">
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
