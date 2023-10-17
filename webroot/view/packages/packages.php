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
<p>
	Use only the <mark>package</mark> name and NOT the combination of <em>category</em>/<mark>package</mark>
</p>
<form method="get" action="">
	<input type="hidden" name="p" value="packages" />
	<div class="form-group">
		<label class="form-label" for="package">
			Search for a package (<samp>exiv2</samp>).<br />
			Using * as a wildcard (<samp>exi*</samp>) will <i>slow</i> down the query!
		</label>
		<input class="form-input" type="text" placeholder="Use * as a wildcard" id="package" name="ps" value="<?php echo $TemplateData['searchInput']; ?>">
	</div>
	<div class="form-group">
		<input class="btn" type="submit" value="Search">
	</div>
</form>

<?php include_once 'view/system/pagination_fe.inc.php'; ?>

<table class="table table-scroll">
	<thead>
	<tr>
		<th>Category</th>
		<th>Name</th>
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
				<td><a href="index.php?p=category&id=<?php echo $entry['category_id'] ?>"><?php echo $entry['categoryName']; ?></a></td>
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
