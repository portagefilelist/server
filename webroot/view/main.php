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
 * 2023 - 2024 https://www.bananas-playground.net/projekt/portagefilelist/
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'">

	<link rel="stylesheet" href="view/asset/uikit/uikit.min.css">
	<link rel="stylesheet" href="view/asset/style.css">
    <script src="view/asset/uikit/uikit.min.js"></script>
    <script src="view/asset/uikit/uikit-icons.min.js"></script>

	<meta name="author" content="https://www.bananas-playground.net/" />
	<title><?php echo $TemplateData['pageTitle']; ?> / PFL - Portagefilelist.de</title>
	<meta name="keywords" content="gentoo, portage, emerge, package, pfl, e-file" />
    <meta name="description" content="Portage File List collects which files are installed by which ebuild on users machines. It shares this data publicly for searching/browsing. It allows user to search for files that are not installed on their system and figure out which ebuild they need to install in order to obtain it.">

	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<link rel="search" type="application/opensearchdescription+xml" title="PFL – Files" href="/opensearch/files.xml">
	<link rel="search" type="application/opensearchdescription+xml" title="PFL – Packages" href="/opensearch/packages.xml">
	<link rel="search" type="application/opensearchdescription+xml" title="PFL – Categories" href="/opensearch/categories.xml">
</head>
<body class="uk-container">

	<header>
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
	<hr>
	<footer>
		<div class="uk-text-small">
            Copyright &copy; 2023 - <?php echo date("Y"); ?> Portagefilelist - <a href="index.php?p=imprint">Imprint</a><br />
            <a href="https://www.gentoo.org/" target="_blank">Gentoo</a>
        </div>
	</footer>
</body>
</html>
