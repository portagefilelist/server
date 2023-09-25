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

<code>emerge app-portage/pfl</code>
<br /> <br />

<p>
	This will install a cron job that submits new data to the PFL servers every week.
	Don't worry, your privacy mains protected as we are not collecting anything else
	than portage data, and we don't store who sends what.
</p>

<h2>Limitations</h2>
<ul>
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
