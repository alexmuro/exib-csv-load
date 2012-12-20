<?php
date_default_timezone_set('America/New_York');
function checkFile($filename,$fips)
{
  echo "<br>Check File<br>";
  if (file_exists($filename)) { 
  $path_parts = pathinfo($filename);
  echo "loc:".$path_parts['dirname']."<br>";
  echo "file:".$path_parts['basename']."<br>";
  echo "size : " . filesize($filename) . " bytes<br>";
  echo "last modified: " . date ("m/d/y H:i:s.", filemtime($filename))."<br>";
  return 1;
  }
  else
  {
    echo $filename." does not exist.";
    return 0;
  }
}

function loadFile($filename)
{
  $fileID = checkFile($filename,$infips);
  if(!$fileID)
    return 0;

  
  $lines = 0;
  $handle = fopen($filename, "r");
  while(!feof($handle)){
    $line = fgets($handle);
    $lines++;
  }
  fclose($handle);
  echo $linecount;

    
  $fh = fopen($filename, 'r');
  $tempCount = 0;
  $count = 0;
  $total_count = 0;
  echo "# Objects: $lines <br>";
  echo "Type    GEO     Meta<br>";
  $accounts = array();

  if ($fh) 
  {
    echo "Loading $filename. <br>";
    $theData = fgets($fh);
    while(!feof($fh))
    {
      $theData = fgets($fh);
      $id = strtok($theData,',');
      $company = strtok(',');
      $boothnum = strtok(',');
      $email= strtok(',');
      $password=  strtok(',');
      echo "$id | $company | $boothnum | $email | $password | ". md5($password) ."<br>";
      $count++;



// =concatenate("$accounts[] = array('importid' => '",A2,"', 'name' => '",E2,"', 'email' => '",F2,"','password' => '",H2,"' );")
      $accounts[] = array('importid' => $id , 'name' => $company, 'email' => $email,'password' => $password );
      echo "$count / $lines </br>";
    }
  }
  fclose($fh);
  echo "Loading $filename completed.";
  echo "<pre>";
  //print_r($accounts);
  echo "</pre>";
  $count = 0;

$mysql_username = '471712_yah10';
$mysql_host   = 'db1.marketart.com';
$mysql_password = 'P5XSTbDv';
$mysql_database = '471712_yah10';

$conn = mysql_connect($mysql_host, $mysql_username, $mysql_password) or die ("Could not connect: " . mysql_error());
mysql_select_db($mysql_database,$conn);

$alreadyin = '';
$nocofound = '';

$numofaccts = count($accounts);

$numofacctscreated = 0;


for ($i=1; $i < $numofaccts; $i++) {  //


  $query = "update tbl_accounts set password='".md5($accounts[$i]['password'])."' where username='".$accounts[$i]['email']."'";
  $acctresult = mysql_query($query);
  echo($acctresult.'xx'.$query.';<br>');


}

echo '# of accounts created: ' . $numofacctscreated.'<br /><br /><br />';

echo $nocofound . '<br /><br /><br />';

echo $alreadyin . '<br /><br />Total already added: ' . $count;
  
}

loadFile('audiologynow.csv');
?>

