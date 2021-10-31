<?php

//----------------------------------------------------------------------
// Config Variables
//----------------------------------------------------------------------
$host = "127.0.0.1";		// MySQL Settings
$user = "root";
$pass = "";
$database = "vb_lotto";     

$dblink = @mysql_connect($host,$user,$pass);
mysql_select_db($database,$dblink);

if (mysql_query("UPDATE `vb_lotto`.`tickets` SET `used` = '0' WHERE `tickets`.`used` = 1;"))
{
	echo "All tickets reset.";
}	

?>