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
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<link rel="stylesheet" href="view/asset/css/spectre/spectre.min.css">
	<link rel="stylesheet" href="view/asset/css/spectre/spectre-exp.min.css">
	<link rel="stylesheet" href="view/asset/css/spectre/spectre-icons.min.css">

	<link rel="stylesheet" href="view/asset/css/style.css">

	<meta name="author" content="https://www.bananas-playground.net/" />
	<title>PFL / <?php echo $TemplateData['pageTitle']; ?> / Portagefilelist.de</title>

	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
</head>
<body>

	<header class="navbar">
		<?php require_once $ViewMenu; ?>
	</header>

	<main>
		<section>
			<?php require_once $ViewMessage; ?>
		</section>
		<section>
			<?php require_once $View; ?>
		</section>
	</main>

	<footer>
		<div class="text-small">&copy; 2023 - <a href="index.php?p=imprint">Imprint</a></div>
	</footer>
</body>
</html>
