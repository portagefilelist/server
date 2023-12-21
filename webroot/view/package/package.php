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
<h1>Package: <?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?></h1>
<table class="table table-striped table-hover">
	<tr>
		<td>Name</td>
		<td><?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?></td>
	</tr>
	<tr>
		<td>Category</td>
		<td><a href="index.php?p=category&id=<?php echo $TemplateData['package']['categoryId'] ?? '' ?>"><?php echo $TemplateData['package']['categoryName'] ?? ''; ?></a></td>
	</tr>
	<tr>
		<td>Version</td>
		<td><?php echo $TemplateData['package']['version'] ?? ''; ?></td>
	</tr>
	<tr>
		<td>Seen Arch</td>
		<td><?php echo $TemplateData['package']['arch'] ?? ''; ?></td>
	</tr>
	<tr>
		<td>Seen Use</td>
		<td>
			<?php
			if(!empty($TemplateData['package']['usewords'])) {
				foreach($TemplateData['package']['usewords'] as $key=>$entry) {
			?>
				<a target="_blank" href="https://packages.gentoo.org/useflags/<?php echo urlencode($entry); ?>" class="useLink"><?php echo $entry; ?> <i class="icon icon-link"></i></a>
			<?php
				}
			 }
			?>
		</td>
	</tr>
	<tr>
		<td>Seen installs <small>very rough number</small></td>
		<td><?php echo $TemplateData['package']['importcount'] ?? ''; ?></td>
	</tr>
	<tr>
		<td>Other Version/arch</td>
		<td>
			<?php
			if(!empty($TemplateData['package']['otherVersions'])) {
			?>
			<ul class="ulInTable">
			<?php
				foreach($TemplateData['package']['otherVersions'] as $key=>$entry) {
			?>
				<li><a href="index.php?p=package&id=<?php echo ($key); ?>"><?php echo $entry['categoryName'].'/'.$entry['name']; ?></a> <?php echo $entry['version'] ?> <?php echo $entry['arch'] ?></li>
			<?php
				}
			?>
			</ul>
			<?php
			 }
			?>
		</td>
	</tr>
	<tr>
		<td>Gentoo package website</td>
		<td><a href="https://packages.gentoo.org/packages/<?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?>" target="_blank">external <i class="icon icon-link"></i></a></td>
</table>

<div class="divider"></div>

<h2 id="panchor">Package files</h2>

<form method="get" action="#panchor">
	<input type="hidden" name="p" value="package">
	<input type="hidden" name="id" value="<?php echo $TemplateData['package']['hash']; ?>">
	<div class="form-group">
		<label class="form-label" for="filename">
			Search for a file by a filename (<samp>slice.hpp</samp>) or path (<samp>/usr/include/exiv2/slice.hpp</samp>).
			Using * as a wildcard (<samp>slice.*</samp>) (<samp>/usr/include/exiv2/*</samp>) will <i>slow</i> down the query!
		</label>
	</div>
	<div class="input-group">
		<a class="btn btn-primary input-group-btn" href="index.php?p=package&id=<?php echo $TemplateData['package']['hash']; ?>#panchor">Reset</a>
		<input class="form-input" type="text" name="ps" id="filename" placeholder="Search within current package" value="<?php echo $TemplateData['searchInput'] ?? ''; ?>">
		<button class="btn btn-primary input-group-btn">Search</button>
	</div>
</form>

<?php include_once 'view/system/pagination_fe.inc.php'; ?>

<table class="table">
	<thead>
	<tr>
		<th role="columnheader">Filename</th>
		<th role="columnheader">Filepath</th>
	</tr>
	</thead>
	<tbody>

<?php
if(!empty($TemplateData['files'])) {
	foreach($TemplateData['files']['results'] as $key=>$entry) {
?>

					<tr>
						<td><?php echo $entry['name']; ?></td>
						<td><?php echo $entry['path']; ?></td>
					</tr>

<?php
	}
 }
?>
	</tbody>
</table>
