Lenny Blacklist Mod is being uninstalled.<br>
<?php



// drop the table
$sql = "DROP TABLE IF EXISTS itslenny";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_freepbx( "Can not delete table: " . $check->getMessage() .  "\n");
}

?>
