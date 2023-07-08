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
<section class="navbar-section">
	<img src="view/asset/pfl-logo.png" width="50px" /><a href="index.php" class="btn btn-link <?php if($_requestMode == "home") echo 'active'; ?>">Home</a>
	<a href="index.php?p=packages" class="btn btn-link <?php if($_requestMode == "packages") echo 'active'; ?>">Packages</a>
	<a href="index.php?p=categories" class="btn btn-link <?php if($_requestMode == "categories") echo 'active'; ?>">Categories</a>
	<a href="index.php?p=stats" class="btn btn-link <?php if($_requestMode == "stats") echo 'active'; ?>">Stats</a>
	<a href="index.php?p=about" class="btn btn-link <?php if($_requestMode == "about") echo 'active'; ?>">About</a>
</section>

