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
 * 2023 - 2024 https://www.bananas-playground.net/projekt/portagefilelist/
 */
?>
<h1>Packages</h1>
<p>
    Use only the <mark>package</mark> name and NOT the combination of <em>category</em>/<mark>package</mark>
</p>
<form method="get" action="#panchor" id="panchor" class="uk-form-stacked">
    <div class="uk-margin">
        <label class="uk-form-label" for="package">
            Search for a package (<samp>exiv2</samp>).<br />
            Using * as a wildcard (<samp>exi*</samp>) will <i>slow</i> down the query!
        </label>
        <div class="uk-form-controls">
            <input class="uk-input" type="text" placeholder="Searchterm. Use * as a wildcard" id="package" name="ps" value="<?php echo $TemplateData['searchInput']; ?>">
        </div>
    </div>
    <div class="uk-margin">
        <input type="hidden" name="p" value="packages" />
        <input class="uk-button uk-button-primary" type="submit" value="Search">
    </div>
</form>

<?php include_once 'view/system/pagination_fe.inc.php'; ?>

<table class="uk-table uk-table-striped">
    <thead>
    <tr>
        <th role="columnheader">Category</th>
        <th role="columnheader">Name</th>
        <th role="columnheader" class="uk-width-small@s uk-width-auto">Version</th>
        <th role="columnheader" class="uk-width-small@s uk-width-auto">Arch</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(isset($TemplateData['searchresults']['results']) && !empty($TemplateData['searchresults']['results'])) {
        foreach($TemplateData['searchresults']['results'] as $key=>$entry) {
            ?>
            <tr>
                <td><a href="index.php?p=category&id=<?php echo $entry['categoryId'] ?>"><?php echo $entry['categoryName']; ?></a></td>
                <td><a href="index.php?p=package&id=<?php echo $entry['hash']; ?>"><?php echo $entry['categoryName'] ?? ''; ?>/<?php echo $entry['name'] ?? ''; ?></a></td>
                <td><?php echo $entry['version'] ?? ''; ?></td>
                <td><?php echo $entry['arch'] ?? ''; ?></td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>
