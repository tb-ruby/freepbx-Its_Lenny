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

// Check FreePBX db engine
if($amp_conf["AMPDBENGINE"] != "mysql")  {
	echo "This module has not been tested on systems not running MySql. File reports at http://pbxossa.org";
	}

// The following lines define the table name and an array of column names for the database. Adding, removing and updating the
// database is done automatically based on these definitions
$tablename = "itslenny";
$cols['id'] = "INTEGER NOT NULL PRIMARY KEY DEFAULT 1";
$cols['enable'] = "varchar(10) default NULL";
$cols['record'] = "varchar(10) default NULL";
$cols['destination'] = "varchar(100) default NULL";
$cols['description'] = "varchar(100) default NULL";
$cols['silence'] = "INTEGER default NULL";
$cols['itterations'] = "INTEGER default NULL";

// create a table if none present and populate with single temp column
$sql = "CREATE TABLE IF NOT EXISTS $tablename (id INTEGER );";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create table: " . $check->getMessage() .  "\n");
}

//check to see that columns are defined properly and dropp unnecessary columns
//must first drop primary keys in order to edit columns. define primary key(s) above in $col defines
$sql = "ALTER IGNORE TABLE $tablename DROP PRIMARY KEY";  
$check = $db->query($sql);  //ignoring all/any errors from this
$curret_cols = array();
$sql = "DESC $tablename";
$res = $db->query($sql);
while($row = $res->fetchRow())  {
	if(array_key_exists($row[0],$cols)) {
		$curret_cols[] = $row[0];
		//make sure it has the latest definition
		$sql = "ALTER TABLE $tablename MODIFY ".$row[0]." ".$cols[$row[0]];
		$check = $db->query($sql);
		if (DB::IsError($check)) {
			die_freepbx( "Can not update column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
	} else {
		//remove the column
		$sql = "ALTER TABLE $tablename DROP COLUMN ".$row[0];
		$check = $db->query($sql);
		if(DB::IsError($check)) {
			echo "Can not remove column ".$row[0].": " . $check->getMessage() .  "<br>";  //not fatal error
		} else {
			print 'Removed unused column '.$row[0].' from trunkbalance table.<br>';
		}
	} 
}

//add any missing columns that are not already in the table
foreach($cols as $key=>$val)  {
	if(!in_array($key,$curret_cols)) {
		$sql = "ALTER TABLE $tablename ADD ".$key." ".$val;
		$check = $db->query($sql);
		if (DB::IsError($check)) {
			die_freepbx( "Can not add column ".$key.": " . $check->getMessage() .  "<br>");
		} else {
			print 'Added column '.$key.' to '. $tablename.' table.<br>';
		}
	}
}

// Populate table with default values if this is a new install
$sql = "SELECT COUNT(*) FROM $tablename";
$check = $db->getAll($sql,DB_FETCHMODE_ASSOC);
if ($check['0']['COUNT(*)'] == 0) {
	$sql = "INSERT INTO $tablename (id, destination, silence, itterations) VALUES('1','lenny@sip.itslenny.com', '1500', '1')";
	$check = $db->query($sql);
	if (DB::IsError($check)) {
			echo "Can not populate settings with default values<br>";
	} else {
			echo "Populating settings with default values<br>";
	}
} else {
	echo "Migrating existing settings<br>";
}
?>
