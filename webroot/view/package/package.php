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
if(empty($TemplateData['package'])) {
	return;
}
?>
<h1>Package: <?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?></h1>
<table class="uk-table uk-table-striped">
    <tr>
        <td class="uk-width-medium@s uk-width-auto">Name</td>
        <td><?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?></td>
    </tr>
    <tr>
        <td>Category</td>
        <td><a href="index.php?p=category&id=<?php echo $TemplateData['package']['categoryId'] ?? '' ?>"><?php echo $TemplateData['package']['categoryName'] ?? ''; ?></a></td>
    </tr>
    <tr>
        <td>Version</td>
        <td>
            <?php echo $TemplateData['package']['version'] ?? ''; ?>

            <?php
            if(!empty($TemplateData['package']['topicality'])) {
                echo '<small>Available in repo.</small>';
            } else {
                echo '<small>Availability not known.</small>';
                if(!empty($TemplateData['package']['topicalityLastSeen'])) {
                    echo ' <small>Last seen: '.$TemplateData['package']['topicalityLastSeen'].'</small>';
                }
            }
            ?>
        </td>
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
                <a target="_blank" href="https://packages.gentoo.org/useflags/<?php echo urlencode($entry); ?>"><?php echo $entry; ?> <span uk-icon="link-external"></span></a>&#x20;
            <?php
                }
             }
            ?>
        </td>
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
        <td>Repository</td>
        <td>
            <?php
            if($TemplateData['package']['repository'] == "gentoo") { ?>
                <a href="https://packages.gentoo.org/packages/<?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?>" target="_blank">Gentoo <span uk-icon="link-external"></span></a>
            <?php } elseif($TemplateData['package']['repository'] == "guru") { ?>
                <a href="https://gitweb.gentoo.org/repo/proj/guru.git/tree/<?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?>" target="_blank">GURU <span uk-icon="link-external"></span></a>
            <?php } else { ?>
                <?php echo $TemplateData['package']['repository'] ?? ''; ?> :: <?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?>
            <?php } ?>
        </td>
	</tr>
</table>

<h2 id="panchor">Package files</h2>

<form method="get" action="#panchor" class="uk-form-stacked">
    <div class="uk-margin">
        <label class="uk-form-label" for="filename">
            Search for a file by a filename (<samp>slice.hpp</samp>) or path (<samp>/usr/include/exiv2/slice.hpp</samp>).<br />
            Using * as a wildcard (<samp>slice.*</samp>) (<samp>/usr/include/exiv2/*</samp>) will <i>slow</i> down the query!
        </label>
        <div class="uk-form-controls">
            <input class="uk-input" type="text" name="ps" id="filename" placeholder="Search within current package" value="<?php echo $TemplateData['searchInput'] ?? ''; ?>">
        </div>
    </div>
    <div class="uk-margin">
        <input type="hidden" name="p" value="package">
        <input type="hidden" name="id" value="<?php echo $TemplateData['package']['hash'] ?? ''; ?>">
        <input class="uk-button uk-button-primary" type="submit" value="Search">
        <a class="uk-button uk-button-default" href="index.php?p=package&id=<?php echo $TemplateData['package']['hash'] ?? ''; ?>#panchor">Reset</a>
    </div>
</form>

<?php include_once 'view/system/pagination_fe.inc.php'; ?>

<table class="uk-table uk-table-striped">
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
