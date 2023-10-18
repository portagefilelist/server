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
?>
<div class="container">
    <div class="columns">
        <div class="column col-lg-12 hide-sm">
			<p>
				Portage File List collects which files are installed by which ebuild on users machines.<br />
				It shares this data publicly for searching/browsing. It allows user to search for files that are not
				installed on their system and figure out which ebuild they need to install in order to obtain it.<br />
				A more detailed description what this site is about, can be <a href="index.php?p=about">read here</a>.<br />
			</p>
        </div>
        <div class="column col-lg-12 hide-sm">

<?php if(!empty($TemplateData['topSearch'])) { ?>
<p>
	Latest top searches:
	<?php foreach($TemplateData['topSearch'] as $amount=>$value) {
		echo '<span class="chip"><a href="index.php?fs='.$value.'">'.$value.'</a></span>';
	}
	?>
</p>
<?php } ?>

<?php if(!empty($TemplateData['latestPackages'])) { ?>
<p>
	Latest packages:
	<?php foreach($TemplateData['latestPackages'] as $key=>$entry) {
		echo '<span class="chip"><a href="index.php?p=package&id='.$entry['hash'].'">'.$entry['name'].'</a></span>';
	}
	?>
</p>
<?php } ?>

        </div>
    </div>
</div>

<form method="get" action="#panchor" id="panchor">
	<div class="form-group">
		<label class="form-label" for="filename">
			Search for a package by a filename (<samp>slice.hpp</samp>) or path (<samp>/usr/include/exiv2/slice.hpp</samp>).<br />
			Using * as a wildcard (<samp>slice.*</samp>) (<samp>/usr/include/exiv2/*</samp>) will <i>slow</i> down the query!
		</label>
		<input class="form-input" type="text" placeholder="Use * as a wildcard" id="filename" name="fs" value="<?php echo $TemplateData['searchInput']; ?>">
	</div>
	<div class="form-group">
		<label class="form-switch">
			<input type="checkbox" name="unique" value="1" <?php echo $TemplateData['searchUnique']; ?>>
			<i class="form-icon"></i> Unique packages
		</label>
	</div>
	<div class="form-group">
		<input class="btn" type="submit" value="Search">
	</div>
</form>

<?php include_once 'view/system/pagination_fe.inc.php'; ?>

<table class="table">
	<thead>
		<tr>
			<th>Filename</th>
			<th>Filepath</th>
			<th>Category</th>
			<th>Package</th>
			<th>Version</th>
			<th>Arch</th>
		</tr>
	</thead>
	<tbody>
<?php
	if(isset($TemplateData['searchresults']['results']) && !empty($TemplateData['searchresults']['results'])) {
		foreach($TemplateData['searchresults']['results'] as $key=>$entry) {
?>
		<tr>
			<td><?php echo $entry['name'] ?? ''; ?></td>
			<td><?php echo $entry['path'] ?? ''; ?></td>
			<td>
				<a href="index.php?p=category&id=<?php echo $entry['category_id'] ?? ''; ?>"><?php echo $entry['categoryName']; ?></a>
			</td>
			<td>
				<a href="index.php?p=package&id=<?php echo $entry['package_id']; ?>"><?php echo $entry['categoryName']; ?>/<?php echo $entry['packageName']; ?></a>
			</td>
			<td><?php echo $entry['packageVersion'] ?? ''; ?></td>
			<td><?php echo $entry['packageArch'] ?? ''; ?></td>
		</tr>
<?php
		}
	}
?>
	</tbody>
</table>
