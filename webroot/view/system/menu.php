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
<section class="navbar-section">
	<img src="view/asset/pfl-logo.png" width="50px" /><a href="index.php" class="btn btn-link <?php if($_requestMode == "home") echo 'active'; ?>">Home</a>
	<a href="index.php?p=packages" class="btn btn-link <?php if($_requestMode == "packages") echo 'active'; ?>">Packages</a>
	<a href="index.php?p=categories" class="btn btn-link <?php if($_requestMode == "categories") echo 'active'; ?>">Categories</a>
	<a href="index.php?p=stats" class="btn btn-link <?php if($_requestMode == "stats") echo 'active'; ?>">Stats</a>
	<a href="index.php?p=about" class="btn btn-link <?php if($_requestMode == "about") echo 'active'; ?>">About</a>
</section>
