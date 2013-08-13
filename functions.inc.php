<?php /* $Id */

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



//  check for settings and return
function itslenny_config() {
	$sql = "SELECT * FROM itslenny WHERE `id` = '1'";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	return is_array($results)?$results:array();
}

// store settings
function itslenny_edit($id,$post){
	global $db;

	$var1 = $db->escapeSimple($post['enable']);
	$var2 = $db->escapeSimple($post['record']);
	$var3 = $db->escapeSimple($post['destination']);
	$var4 = $db->escapeSimple($post['silence']);
	$var5 = $db->escapeSimple($post['itterations']);

	$results = sql("
		UPDATE itslenny 
		SET 
			enable = '$var1', 
			record = '$var2', 
			destination = '$var3',
			silence = '$var4',
			itterations = '$var5'
		WHERE id = '$id'");

		needreload();
}

function itslenny_hookGet_config($engine) {

	// This generates the dialplan
	global $ext;
	global $asterisk_conf;
	switch($engine) {
		case "asterisk":
			$config = itslenny_config();
			$context = "app-blacklist-check";
			$exten = "s";

			if ($config[0]['enable']=='CHECKED') {
				if ($config[0]['record']=='CHECKED') {
					$ext->splice($context, $exten, 4, new ext_gosub('1', 's', 'sub-record-check', 'rg,s,always'));
				}
				$ext->splice($context, $exten, 5, new ext_goto('1', 's', 'app-nv-itslenny'));
				$ext->splice($context, $exten, 6, new ext_hangup);
			}
			
			$id = "app-nv-itslenny";
			$c = "s";
			$config = itslenny_config();
			$silence = $config[0]['silence'];
			$itterations = $config[0]['itterations'];
			
			$ext->addInclude('from-internal-additional', $id);
			$ext->add($id, $c, '', new ext_answer(''));
			if ($config[0]['record']=='CHECKED') {
				$ext->add($id, $c, '', new ext_playback('en/this-call-may-be-monitored-or-recorded'));
			}
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny01'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny02'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny03'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny04'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny05'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny06'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny07'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny08'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny09'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny10'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny11'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny12'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny13'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny14'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny15'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny02'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny03'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny06'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny08'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny09'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny10'));
			$ext->add($id, $c, '', new ext_gosub('playitonce','','','Lenny14'));
			$ext->add($id, $c, '', new ext_playback('en/tt-monkeys'));
			$ext->add($id, $c, '', new ext_hangup);

			$ext->add($id, $c, 'playitonce', new ext_noop('Lenny speaks once'));
			$ext->add($id, $c, '', new ext_playback('lenny/${ARG1}'));
			$ext->add($id, $c, '', new extension("WaitForSilence($silence,$itterations)"));
			$ext->add($id, $c, '', new ext_return());
			
		break;
	}
}

		
function itslenny_vercheck() {
	$newver = false;
	$module_local = itslenny_xml2array("modules/itslenny/module.xml");
	$module_remote = itslenny_xml2array("https://github.com/POSSA/freepbx-Its_Lenny/master/module.xml");
	if ( $module_remote['module']['version'] > $module_local['module']['version']) {
		$newver = true;
	}
	return ($newver);
}

//Parse XML file into an array
function itslenny_xml2array($url, $get_attributes = 1, $priority = 'tag')  {
	$contents = "";
	if (!function_exists('xml_parser_create'))
	{
		return array ();
	}
	$parser = xml_parser_create('');
	if(!($fp = @ fopen($url, 'rb')))
	{
		return array ();
	}
	while(!feof($fp))
	{
		$contents .= fread($fp, 8192);
	}
	fclose($fp);
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);
	if(!$xml_values)
	{
		return; //Hmm...
	}
	$xml_array = array ();
	$parents = array ();
	$opened_tags = array ();
	$arr = array ();
	$current = & $xml_array;
	$repeated_tag_index = array ();
	foreach ($xml_values as $data)
	{
		unset ($attributes, $value);
		extract($data);
		$result = array ();
		$attributes_data = array ();
		if (isset ($value))
		{
			if($priority == 'tag')
			{
				$result = $value;
			}
			else
			{
				$result['value'] = $value;
			}
		}
		if(isset($attributes) and $get_attributes)
		{
			foreach($attributes as $attr => $val)
			{
				if($priority == 'tag')
				{
					$attributes_data[$attr] = $val;
				}
				else
				{
					$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
		}
		if ($type == "open")
		{
			$parent[$level -1] = & $current;
			if(!is_array($current) or (!in_array($tag, array_keys($current))))
			{
				$current[$tag] = $result;
				if($attributes_data)
				{
					$current[$tag . '_attr'] = $attributes_data;
				}
				$repeated_tag_index[$tag . '_' . $level] = 1;
				$current = & $current[$tag];
			}
			else
			{
				if (isset ($current[$tag][0]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array($current[$tag],$result);
					$repeated_tag_index[$tag . '_' . $level] = 2;
					if(isset($current[$tag . '_attr']))
					{
						$current[$tag]['0_attr'] = $current[$tag . '_attr'];
						unset ($current[$tag . '_attr']);
					}
				}
				$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
				$current = & $current[$tag][$last_item_index];
			}
		}
		else if($type == "complete")
		{
			if(!isset ($current[$tag]))
			{
				$current[$tag] = $result;
				$repeated_tag_index[$tag . '_' . $level] = 1;
				if($priority == 'tag' and $attributes_data)
				{
					$current[$tag . '_attr'] = $attributes_data;
				}
			}
			else
			{
				if (isset ($current[$tag][0]) and is_array($current[$tag]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					if ($priority == 'tag' and $get_attributes and $attributes_data)
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array($current[$tag],$result);
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $get_attributes)
					{
						if (isset ($current[$tag . '_attr']))
						{
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
						if ($attributes_data)
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
					}
					$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
				}
			}
		}
		else if($type == 'close')
		{
			$current = & $parent[$level -1];
		}
	}
	return ($xml_array);
}