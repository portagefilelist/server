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

<form method="get" action="">
	<input type="hidden" name="p" value="categories" />
	<div class="form-group">
		<label class="form-label" for="category">
			Search for a category (<samp>media-libs</samp>).<br />
			Using * as a wildcard (<samp>media-li*.*</samp>) will <i>slow</i> down the query!
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
		<th>Name</th>
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