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
<h1>Category details</h1>
<table class="uk-table uk-table-striped">
    <tr>
        <td class="uk-width-medium@s uk-width-auto">Name:</td>
        <td><?php echo $TemplateData['category']['name'] ?? ''; ?></td>
    </tr>
    <tr>
        <td>Gentoo category website:</td>
        <td><a href="https://packages.gentoo.org/categories/<?php echo $TemplateData['category']['name'] ?? ''; ?>/" target="_blank">external <span uk-icon="link-external"></span></a></td>
    </tr>
</table>

<h2 id="panchor">Category packages</h2>

<?php include_once 'view/system/pagination_fe.inc.php'; ?>

<table class="uk-table uk-table-striped">
    <thead>
    <tr>
        <th role="columnheader">Name</th>
        <th role="columnheader" class="uk-width-small@s uk-width-auto">Version</th>
        <th role="columnheader" class="uk-width-small@s uk-width-auto">Arch</th>
    </tr>
    </thead>
    <tbody>

    <?php
    if(!empty($TemplateData['packages'])) {
        foreach($TemplateData['packages']['results'] as $key=>$entry) {
            ?>

            <tr>
                <td><a href="index.php?p=package&id=<?php echo $entry['hash']; ?>"><?php echo $entry['name']; ?></a></td>
                <td><?php echo $entry['version']; ?></td>
                <td><?php echo $entry['arch']; ?></td>
            </tr>

            <?php
        }
    }
    ?>
    </tbody>
</table>
