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
<h1>Category details</h1>
<table class="table table-striped table-hover">
	<tr>
		<td>Name:</td>
		<td><?php echo $TemplateData['category']['name'] ?? ''; ?></td>
	</tr>
	<tr>
		<td>Gentoo category website:</td>
		<td><a href="https://packages.gentoo.org/categories/<?php echo $TemplateData['category']['name'] ?? ''; ?>/" target="_blank">external <i class="icon icon-link"></i></a></td>
</table>

<div class="divider"></div>
<h2>Category packages</h2>

<?php include_once 'view/system/pagination_fe.inc.php'; ?>

<table class="table">
	<thead>
	<tr>
		<th>Name</th>
		<th>Version</th>
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
			</tr>

			<?php
		}
	}
	?>
	</tbody>
</table>
