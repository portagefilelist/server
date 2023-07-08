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
<h1>Statistics</h1>
<p>
	Amount of indexed files: <b><?php echo $TemplateData['f']['amount'] ?? ''; ?></b><br />
	Amount of indexed packages: <b><?php echo $TemplateData['p']['amount'] ?? ''; ?></b><br />
	Indexed architectures: <b><?php echo implode(", ", $TemplateData['p']['arch']); ?></b>
</p>

<div class="container">
	<div class="columns">
		<div class="column col-4 col-xl-12">
			<h3>Latest packages</h3>
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Name</th>
						<th>Uniq name</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if(!empty($TemplateData['p']['latest'])) {
						foreach($TemplateData['p']['latest'] as $key=>$entry) {
							?>
							<tr>
								<td><?php echo $entry['name']; ?></a></td>
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
			<h3>Latest files</h3>
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>File</th>
						<th>Package</th>
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
			<h3>Use top 10</h3>
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Use</th>
						<th>#</th>
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
	</div>
</div>