<?php
error_reporting(E_ALL);
//ini_set('display_errors', '1');
include 'sanitize.php';
include 'sqlfunctions.php';
include 'db.php';


  //--------------------------------------------------------------
  // set variables -- $show_id and $filename passed via ?id=&file=
  //--------------------------------------------------------------
  $show_id = mysql_real_escape_string($_POST['id']);
  $filename = '../files/'.mysql_real_escape_string($_POST['file']);
  $verbose = true;

  //--------------------------------------------------------
  // MAP IMPORT to DB
  // map csv fields to database
  // hard coded, based on NAHB13 file format
  //--------------------------------------------------------
  $field_map_csv = array(
    'Exhibitor ID' => 'importid',
    'Exhibiting As' => 'exhibitingas',
    'Booth' => 'booth_num',
    'Building' => 'building',
    'Floor' => 'floor',
    'Contact Name' => 'firstname',
    'Address 1' => 'address1',
    'Address 2' => 'address2',
    'City' => 'city',
    'State' => 'state',
    'Country' => 'country',
    'Zip' => 'zip',
    'Phone' => 'phone',
    'Fax' => 'fax',
    'Email' => 'email',
    'website' => 'www',
    'Description' => 'description',
    'Category' => 'category'
  );

  // maps hard coded, based on NAHB13
  $map_handling = array(
    'Central' => '1176',
    'South' => '1185',
    'Parking Lot'=>'1176'

  );
  $menuitem_id = '2138';

//----------------------------------------------
// Find What has already been imported
// If it has an imprt_id save it to $loaded
// otherwise load it to $loaded_noid
//----------------------------------------------

// query tbl_exhibitors and tbl_exhibitor show for all existing importids
  $query = "select te.id te_id, te.name te_name, tes.id,tes.importid, tes.menuitem_id, tes.exhibitingas, tes.allowupdate from tbl_exhibitor_show as tes join tbl_exhibitors as te on te.id=tes.exhibitor_id where tes.show_id=$show_id";
  $run = mysql_query($query);

// loop through results and prep array for id matching
while ($row=mysql_fetch_object($run)) {
  // any records missing an importid set are dropped in to $loaded_noid array for secondary matching
  if ($row->importid) {
    $loaded[$row->importid] = array('id'=>$row->id,'importid'=>$row->importid,'menuitem_id'=>$row->menuitem_id,'exhibitingas'=>$row->exhibitingas,'allowupdate'=>$row->allowupdate,'te_id'=>$row->te_id,'te_name'=>$row->te_name);
  } else {
    $loaded_noid[$row->exhibitingas] = array('id'=>$row->id,'menuitem_id'=>$row->menuitem_id,'exhibitingas'=>$row->exhibitingas,'allowupdate'=>$row->allowupdate,'te_id'=>$row->te_id,'te_name'=>$row->te_name);
  } // e importid check

} //e while

//------------------------------------------
// Load Data from CSV File
// into $header & $csv_rows
//--------------------------------------------
$row = 1;
// open csv and loop through data
if (($csv = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($csv, 3000, ",")) !== FALSE) {
        // set header values
        if ($row==1) {
          foreach ($data as $fieldname) {
            $header[]=$fieldname;
          } // e foreach

        } else {
          // if not header row, loop through all other rows
          $cnt = 0;
          foreach ($data as $value) {
            $csv_rows[$row][$field_map_csv[$header[$cnt]]] = $value;

            $cnt++;
          } // e foreach

        } // e if
        $row++; // increment row value
    } // e while

    fclose($csv);
} // e csv processing

//----------------------------------------------------------------
//First, remove exhibitor_booths for all exhibitors in the file 
//(* not all exhibitors for the show)
//Loop through all the rows, get all the import ids, 
//for each import id, match on tbl_exhibitor_show 
//and remove all the exhibitor_booths that match
//----------------------------------------------------------------   
$import_ids = '(';
foreach ($csv_rows as $csv_row) {
    $import_ids .= "'".$csv_row['importid']."',";
}
$import_ids = substr_replace($import_ids ,")",-1);

$query_string = deleteBooths($show_id,$import_ids);
//echo $query_string."<br>";
$run = mysql_query($query_string);
//echo mysql_errno() . ": " . mysql_error() . "\n";
//echo "Deleted ".mysql_affected_rows($run)." rows from tbl_exhibitor_booth.<br>";
//if($verbose) {}

//-----------------------------------------
// START IMPORT
//-----------------------------------------

$no_update = 0;
$update =0; 
$insert = 0; 
foreach ($csv_rows as $csv_row) 
//cycle throughg each row in the csv file
{
$row_count = count($csv_row);
if(isset($loaded[$csv_row['importid']]))
//if this has already been loaded into the database
{
    $tesid = $loaded[$csv_row['importid']]['id'];
    if($loaded[$csv_row['importid']]['allowupdate'] == 1)
    //if allowupdate == 1, only update exhibiting as and booths
    {
      $update_query_string = csvRowNoUpdate($csv_row,$show_id);
      $run = mysql_query($update_query_string) or die(mysql_error());
      $insert_query_string =csvRowBoothInsert($csv_row,$tesid,$map_handling);
      $run = mysql_query($insert_query_string) or die(mysql_error());
      $no_update++;
    }
   else
    //otherwise do full update
    {
      //update tbl_exhibitor_show
      $update_query_string = csvRowUpdate($csv_row,$show_id);
      //echo $update_query_string."</br></br>";
      $run = mysql_query($update_query_string) or die(mysql_error());
      $insert_query_string = csvRowBoothInsert($csv_row,$tesid,$map_handling);
      //echo $insert_query_string."</br></br>";
      $run = mysql_query($insert_query_string) or die(mysql_error());
      $update++; 
    }
  }
  else
  //This row is not currently in the database, fresh insert
  {
    
     $query = "Select id from tbl_exhibitors where name = '".sanitize($csv_row['exhibitingas'])."'";
     echo $query.'<br>';
     $run = mysql_query($query);
      // loop through results and prep array for id matching
      
      if($row=mysql_fetch_object($run))
      {
        $tesid = createExhibitorwithID($row->id,$show_id,$menuitem_id,$csv_row);
        $update_query_string = csvRowUpdate($csv_row,$show_id);
        $run = mysql_query($update_query_string);
        //insert booths
        $insert_query_string = csvRowBoothInsert($csv_row,$tesid,$map_handling);          
        $run = mysql_query($insert_query_string);
      }
      else
      {
        $tesid = createExhibitorNoID($show_id,$menuitem_id,$csv_row);
        $update_query_string = csvRowUpdate($csv_row,$show_id);
        $run = mysql_query($update_query_string);
        //insert booths
        $insert_query_string = csvRowBoothInsert($csv_row,$tesid,$map_handling); 
        $run = mysql_query($insert_query_string);
      }
      
      //insert booths
      $insert_query_string = csvRowBoothInsert($csv_row,$map_handling);
      $insert++;
  }

}
          
echo "</br>CSV Total Rows:".count($csv_rows)."</br>";
echo "__________________________________________</br>";
echo "Rows Updated  :".$update."</br>";
echo "No update Rows:".$no_update."</br>";
echo "Rows Inserterd:".$insert."</br>";
echo "__________________________________________</br>";
echo "Total Rows effected:".($no_update+$update+$insert);


?>