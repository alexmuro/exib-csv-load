<?php
// db setup
$mysql_username = 'root';
$mysql_host   = 'localhost';
$mysql_password = 'am1238wk';
$mysql_database = 'market_art_ems_development';

// db connect
$connect = mysql_connect($mysql_host, $mysql_username, $mysql_password) 
or die ("Could not connect: " . mysql_error());
mysql_select_db($mysql_database,$connect);
?>