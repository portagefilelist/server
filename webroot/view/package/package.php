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
?>
<h1>Package: <?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?></h1>
<table class="table table-striped table-hover">
	<tr>
		<td>Name</td>
		<td><?php echo $TemplateData['package']['categoryName'] ?? ''; ?>/<?php echo $TemplateData['package']['name'] ?? ''; ?></td>
	</tr>
	<tr>
		<td>Category</td>
		<td><a href="index.php?p=category&id=<?php echo $TemplateData['package']['category_id'] ?? '' ?>"><?php echo $TemplateData['package']['categoryName'] ?? ''; ?></a></td>
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
				<a target="_blank" href="https://packages.gentoo.org/useflags/<?php echo urlencode($entry); ?>"><?php echo $entry; ?> <i class="icon icon-link"></i></a>&nbsp;
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
<h2>Package files</h2>

<?php include_once 'view/system/pagination_fe.inc.php'; ?>

<table class="table">
	<thead>
	<tr>
		<th>Filename</th>
		<th>Filepath</th>
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
