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

<form method="get" action="#panchor" id="panchor">
	<input type="hidden" name="p" value="categories" />
	<div class="form-group">
		<label class="form-label" for="category">
			Search for a category (<samp>media-libs</samp>).<br />
			Using * as a wildcard (<samp>media-li*</samp>) will <i>slow</i> down the query!
		</label>
		<input class="form-input" type="text" placeholder="Use * as a wildcard" id="category" name="cs" value="<?php echo $TemplateData['searchInput']; ?>">
	</div>
	<div class="form-group">
		<input class="btn" type="submit" value="Search">
	</div>
</form>

<?php include_once 'view/system/pagination_fe.inc.php'; ?>

<table class="table">
	<thead>
	<tr>
		<th role="columnheader">Name</th>
	</tr>
	</thead>
	<tbody>
	<?php
	if(isset($TemplateData['searchresults']['results']) && !empty($TemplateData['searchresults']['results'])) {
		foreach($TemplateData['searchresults']['results'] as $key=>$entry) {
			?>
			<tr>
				<td><a href="index.php?p=category&id=<?php echo $entry['hash'] ?>"><?php echo $entry['name']; ?></a></td>
			</tr>
			<?php
		}
	}
	?>
	</tbody>
</table>
