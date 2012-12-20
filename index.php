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
$showid = 319;

$numofaccts = count($accounts);

$numofacctscreated = 0;


for ($i=1; $i < $numofaccts; $i++) {  //

  // check to see if the company is already in the system
  $query = "select * from tbl_accounts where username like '%".$accounts[$i]['email']."%'";
  $result = mysql_query($query);
  
  if($result && mysql_num_rows($result) > 0)
  {
    $account_id = mysql_fetch_object($result)->id;
    // they're already in the system, don't add them just yet.  
    $alreadyin .= $accounts[$i]['email'] . ', account_id: ' . $account_id . ', <br />';
    
    if($inst = mysql_query("SELECT id,menuitem_id FROM tbl_exhibitor_show WHERE show_id=$showid AND importid='".$accounts[$i]['importid']."'")){
      if(mysql_num_rows($inst) == 1){
        $inst_obj = mysql_fetch_object($inst);
        $instance = array(
          'show_id'     => $showid,
          'account_id'  => $account_id,
          'menuitem_id' => $inst_obj->menuitem_id,
          'instance_id' => $inst_obj->id
        );
        // check to see if tbl_account_instance exists for this exhibitor account
        if($acct_inst_exists = mysql_query("SELECT id FROM tbl_account_instance WHERE show_id=$showid AND menuitem_id=".$instance['menuitem_id']." AND instance_id=".$instance['instance_id'])){
          if(mysql_num_rows($acct_inst_exists) < 1){
            // INSERT THIS RECORD
            $insert = "INSERT INTO tbl_account_instance (show_id,account_id,menuitem_id,instance_id) VALUES ($showid,{$instance['account_id']},{$instance['menuitem_id']},{$instance['instance_id']})";
            if(mysql_query($insert)){
              $accounts[$i]['insert_instance'] = $insert;
            }
          }
        }
      }
    }
    
    $count++;
  }
  else
  {
    // they are new, see if the company is in the system.
    $query = "select * from tbl_exhibitor_show where importid = '".$accounts[$i]['importid']."' AND show_id=$showid";
    $result = mysql_query($query);

    if($result && mysql_num_rows($result) > 0)
    {
      // company found, got it
      $exhibitor = mysql_fetch_object($result);

      // the exhibitor is in the system, add the account and link them up
      $query = "INSERT INTO tbl_accounts (username, password, name, adminlevel, accounttype) VALUES ('".$accounts[$i]['email']."','".md5($accounts[$i]['password'])."','".$accounts[$i]['name']."','3','normal')";
      $acctresult = mysql_query($query);
      $acctid = mysql_insert_id();

      // here's where we link up the new account to the exhibitor in tbl_account_instance
      $query = "INSERT INTO tbl_account_instance (show_id, account_id, menuitem_id, instance_id, adminlevel) VALUES ('$showid','".$acctid."','1669','".$exhibitor->id."','3')";
      $acctinstanceresult = mysql_query($query);

      // for logging, let's increment the numofacctscreated variable
      $numofacctscreated++;
    }
    else 
    {
      // no company found, log it
      $nocofound .= $accounts[$i]['email'] . ' - ' . $accounts[$i]['importid'] . ',<br />';
    }
  }
}

echo '# of accounts created: X' . $numofacctscreated.'X<br /><br /><br />';

echo 'Y'. $nocofound . 'Y<br /><br /><br />';

echo 'Z'.$alreadyin . 'Z<br /><br />Total already added: ' . $count;
}

loadFile('audiologynow.csv');
?>

