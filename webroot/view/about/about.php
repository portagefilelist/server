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
<h1>About</h1>

<div class="tile">
	<div class="tile-icon">
		<img src="view/asset/pfl-logo.png" width="135px" />
	</div>
	<div class="tile-content">
		<h2 class="tile-title">What is Portage File List?</h2>
		<p>
			Portage File List collects which files are installed by which ebuild on
			users machines. It shares this data publicly for searching/browsing.
			It allows user to search for files that are not installed on their
			system and figure out which ebuild they need to install in order to obtain it.
		</p>
		<p>
			Let's make a short example: You want to use the command brctl, but it's not installed on your system.
			Portage offers no way for you to figure out the name of the ebuild. You have to guess.
			Or you can search PFL and hope that someone else has installed brctl and thus PFL knows which ebuild does that.<br />
			<br />
			Try it: <a href="index.php?fs=brctl&unique=1">query Portagefilelist for brctl</a><br />
			Apparently the ebuild is net-misc/bridge-utils.
		</p>
		<p>
			Gentoo wiki: <a href="https://wiki.gentoo.org/wiki/Pfl" target="_blank">PFL</a><br />
			Gentoo package: <a href="https://packages.gentoo.org/packages/app-portage/pfl" target="_blank">app-portage/pfl</a>
		</p>
	</div>
</div>

<h2>You can help</h2>
<p>
	PFL needs Portage data from your system. The more ebuilds you have installed the better.
	The more exotic ebuilds you have installed the better. Every Gentoo user can help!
</p>
<p>
    <code>emerge -av app-portage/pfl</code>
</p>
<p>
	This will install the <a href="https://packages.gentoo.org/packages/app-portage/pfl" target="_blank">the package</a>, its
    commands and a cron job that submits new data to the PFL servers every week.
	Don't worry, your privacy mains protected as we are not collecting anything else
	than portage data, and we don't store who sends what.
</p>
<h3>Usage</h3>
<p>
    To search for files use either the webseach (this website) or use the <code>e-file</code> command.
    To manually update your portage information, use the command <code>pfl</code>.
    For more options for both of the commands, visit the <a href="https://wiki.gentoo.org/wiki/Pfl" target="_blank">official Gentoo PFL Wiki.</a>
</p>

<h2>Limitations</h2>
<ul>
	<li>Supported repositories are: <a href="https://packages.gentoo.org/">Gentoo</a> and <a href="https://wiki.gentoo.org/wiki/Project:GURU">GURU</a></li>
	<li><a href="https://packages.gentoo.org/useflags/expand" target="_blank">USE Expand</a> useflags will be ignored.</li>
	<li>Files with <i>/usr/src/linux</i>, <i>-gentoo-dist/</i>, <i>*__*</i> will be ignored. The package itself will still be added.</li>
	<li>Only files or symlinks are indexed.</li>
</ul>

<h2>Feedback</h2>

<p>Please visit <a href="https://github.com/portagefilelist/client" target="_blank">PFL Github repository</a> for development.</p>
<p>You have found a bug or have suggestions for improvement? <a href="https://github.com/portagefilelist/client/issues" target="_blank">Let me know!</a></p>

<h2>History</h2>
<ul>
	<li>D. Buschke - Original creator</li>
	<li>vispillo, Ionic and all the other peoples from #gentoo.de@quakenet</li>
	<li>vispillo was implementing the first user of PFL - it was a bot ;-)</li>
	<li>Ionic was the first beta tester of every version</li>
	<li>thanks to all the other peoples of the channel for ignoring my bot tests</li>
	<li>Desastre created an ebuild</li>
	<li>Neza created the PFL logo</li>
	<li>bones7456 created the e-file script which allows you to search files from cmd line</li>
	<li>odi has written the texts of the frontpage</li>
	<li>billie was pulling PFL into the Gentoo portage</li>
	<li>Richard Grenville created/is creating a new python base e-file script</li>
	<li>Eduardo (deb_security) reported a <a href="https://www.openbugbounty.org/reports/541568/" target="_blank">XSS problem</a></li>
</ul>
