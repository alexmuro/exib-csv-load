<?php
// db setup

$mysql_username = '471712_yah10';
$mysql_host   = 'db1.marketart.com';
$mysql_password = 'P5XSTbDv';
$mysql_database = '471712_yah10';


// $mysql_username = 'root';
// $mysql_host   = 'localhost';
// $mysql_password = 'am1238wk';
// $mysql_database = '471712_y_376';


// db connect
$connect = mysql_connect($mysql_host, $mysql_username, $mysql_password) 
or die ("Could not connect: " . mysql_error());
mysql_select_db($mysql_database,$connect);
?>