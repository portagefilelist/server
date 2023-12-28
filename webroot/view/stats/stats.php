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
<h1>Statistics</h1>
<p>
	Repositories indexed: <a href="https://packages.gentoo.org/">Gentoo</a> and <a href="https://wiki.gentoo.org/wiki/Project:GURU">GURU</a><br />
	Amount of indexed packages: <b><?php echo $TemplateData['p']['amount'] ?? ''; ?></b><br />
	Amount of indexed files: <b><?php echo $TemplateData['f']['amount'] ?? ''; ?></b><br />
	Indexed architectures: <b><?php echo implode(", ", $TemplateData['p']['arch']); ?></b>
</p>

<div class="container">
	<div class="columns col-bottom-gap">
		<div class="column col-4 col-xl-12">
			<h3>Top successful file searches</h3>
			<table class="table table-striped table-hover">
				<thead>
				<tr>
					<th role="columnheader">Name</th>
					<th role="columnheader">#</th>
				</tr>
				</thead>
				<tbody>
				<?php
				if(!empty($TemplateData['topFSearch'])) {
					foreach($TemplateData['topFSearch'] as $amount=>$value) {
						?>
						<tr>
							<td><?php echo $value; ?></a></td>
							<td><?php echo $amount; ?></td>
						</tr>
						<?php
					}
				}
				?>
				</tbody>
			</table>
		</div>
		<div class="column col-4 col-xl-12">
			<h3>Latest updated packages</h3>
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th role="columnheader">Name</th>
						<th role="columnheader">Unique name</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if(!empty($TemplateData['p']['latest'])) {
						foreach($TemplateData['p']['latest'] as $key=>$entry) {
							?>
							<tr>
								<td><?php echo $entry['name']; ?></td>
								<td><a href="index.php?p=package&id=<?php echo $entry['hash']; ?>"><?php echo $entry['categoryName']; ?>/<?php echo $entry['name']; ?></a></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
		<div class="column col-4 col-xl-12">
			<h3>Latest updated files</h3>
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th role="columnheader">File</th>
						<th role="columnheader">Package</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if(!empty($TemplateData['f']['latest'])) {
						foreach($TemplateData['f']['latest'] as $key=>$entry) {
							?>
							<tr>
								<td><?php echo $entry['name']; ?></a></td>
								<td><a href="index.php?p=package&id=<?php echo $entry['hash']; ?>"><?php echo $entry['categoryName']; ?>/<?php echo $entry['packageName']; ?></a></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
		<div class="column col-4 col-xl-12">
			<h3>USE flag top 10</h3>
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th role="columnheader">Use</th>
						<th role="columnheader">#</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if(!empty($TemplateData['p']['use'])) {
						foreach($TemplateData['p']['use'] as $key=>$entry) {
							?>
							<tr>
								<td><?php echo $entry['useword']; ?></a></td>
								<td><?php echo $entry['amount']; ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
        <div class="column col-4 col-xl-12">
            <h3>Most installs</h3>
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th role="columnheader">Name</th>
                    <th role="columnheader">#</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(!empty($TemplateData['p']['install'])) {
                    foreach($TemplateData['p']['install'] as $key=>$entry) {
                        ?>
                        <tr>
                            <td><a href="index.php?p=package&id=<?php echo $entry['hash']; ?>"><?php echo $entry['categoryName']; ?>/<?php echo $entry['name']; ?></a></td>
                            <td><?php echo $entry['importcount']; ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
	</div>
</div>
