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

if(!empty($messageData)) {
	$cssClass="toast-primary";
	if(isset($messageData['status'])) {
		$cssClass="toast-".$messageData['status'];
	}
	$message = $messageData['message'];
	if(is_array($message)) {
		$message = implode("<br />", $message);
	}

	if(!empty($message)) {
?>
	<div class="toast <?php echo $cssClass; ?>"><?php echo $message; ?></div>
<?php
	}
}
?>
