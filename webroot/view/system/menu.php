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
 * 2023 - 2025 https://www.bananas-playground.net/projekt/portagefilelist/
 */
?>
<nav class="uk-navbar-container">
    <div class="uk-container">
        <div class="uk-navbar">
            <div class="uk-navbar-left">
                <a class="uk-navbar-item uk-logo" href="index.php" aria-label="Back to Home"><img src="asset/pfl-logo.png" width="50px" height="50px" alt="PFL Logo" /></a>
                <ul class="uk-navbar-nav">
                    <li class="<?php if($_requestMode == "packages") echo 'uk-active'; ?>">
                        <a href="index.php?p=packages">Packages</a>
                    </li>
                    <li class="<?php if($_requestMode == "categories") echo 'uk-active'; ?>">
                        <a href="index.php?p=categories">Categories</a>
                    </li>
                    <li class="<?php if($_requestMode == "stats") echo 'uk-active'; ?>">
                        <a href="index.php?p=stats">Stats</a>
					</li>
                    <li class="<?php if($_requestMode == "about") echo 'uk-active'; ?>">
                        <a href="index.php?p=about">About</a>
                    </li>
	                <li class="<?php if($_requestMode == "archive") echo 'uk-active'; ?>">
		                <a href="index.php?p=archive">Archive</a>
	                </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
