<?php 

if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//This file is part of FreePBX.
//
//    This is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    This module is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    see <http://www.gnu.org/licenses/>.
//

// check to see if user has automatic updates enabled in FreePBX settings
$cm =& cronmanager::create($db);
$online_updates = $cm->updates_enabled() ? true : false;

// check dev site to see if new version of module is available
if ($online_updates && $foo = itslenny_vercheck()) {
	print "<br>A <b>new version of this module is available</b> from the <a target='_blank' href='http://pbxossa.org'>PBX Open Source Software Alliance</a><br>";
}

// check form and define var for form action
isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//if submitting form, update database
if(isset($_POST['submit'])) {
		itslenny_edit(1,$_POST);
		redirect_standard();
	
	}

$module_local = itslenny_xml2array("modules/itslenny/module.xml");
$module_version = $module_local['module']['version'];
$config = itslenny_config();

?>

<h2>Its Lenny Module</h2>

<form autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" >
<table>
		<tr>			
			<td colspan="2">			
			    <?php echo _("This module is used to modify the standard FreePBX blacklist so that banned callers interact with a series of recordings attempting to fool them into thinking it is a real person. The sound files were originally developed for the online service, <a href='http://www.itslenny.com'>Its Lenny</a> and have been made available to users free of charge by the owner. The Asterisk dial plan for this module was developed by Ward Mundy at <a href='http://www.nerdvittles.com'>Nerd Vittles</a>."); ?>
			</td>			
		</tr>
</table>
<?php

//if submitting form, update database
if(isset($_POST['submit'])) {
		itslenny_edit(1,$_POST);
}
$html = "<table>";
$html .= "<tr><td colspan='2'><h5><a href='#' class='info'>Lenny Blacklist Mod Config<span>This is used to modify the FreePBX blacklist module so that blacklisted callers are automatically intercepted by the Its Lenny recordings</span></a><hr></h5></td></tr>";
$html .= "<tr>";
$html .= "<td><a href='#' class='info'>Enable Its Lenny<span>If this is disabled, the blacklist reverts to default behavior.</span></a></td>";
$html .= "<td><input type='checkbox' name='enable' value='CHECKED' ".$config[0]['enable']."></td>";
$html .= "</tr><tr>";
$html .= "<td><a href='#' class='info'>Enable Recording<span>If enabled, the call is recorded locally and a notice is streamed to callers that they are being recorded.</span></a></td>";
$html .= "<td><input type='checkbox' name='record' value='CHECKED' ".$config[0]['record']."></td>";
$html .= "</tr><tr>";
$html .= "<td><a href='#' class='info'>Silence Detection Delay<span>Default = 1500, this value in milliseconds is how how much silence must elapse before it is considered a gap.</span></a></td>";
$html .= "<td><input type='text' name='silence' size=10 value='".htmlspecialchars(isset($config[0]['silence']) ? $config[0]['silence'] : '')."' ></td>";
$html .= "</tr><tr>";
$html .= "<td><a href='#' class='info'>Silence Detection Itterations<span>Default = 1, this is how many silence gaps must elapse before Lenny responds.</span></a></td>";
$html .= "<td><input type='text' name='itterations' size=10 value='".htmlspecialchars(isset($config[0]['itterations']) ? $config[0]['itterations'] : '')."' ></td>";
$html .= "</tr>";
$html .= "</table>";

echo $html;

?>
<table>
	<tr>
		<td colspan="2"><br><h6><input name="submit" type="submit" value="<?php echo _("Submit Changes")?>" ></h6></td>
	</tr>
</table>
</form>

<p align="center" style="font-size:11px;">Its Lenny Module version <?php echo $module_version ?><br>
The module is maintained by the developer community at the <a target="_blank" href="https://github.com/POSSA/freepbx-its_lenny"> PBX Open Source Software Alliance</a><br></p>

