<?php
include 'sanitize.php';
include 'db.php';

  //--------------------------------------------------------------
  // set variables -- $show_id and $filename passed via ?id=&file=
  //--------------------------------------------------------------
  $show_id = mysql_real_escape_string($_GET['id']);
  $filename = mysql_real_escape_string($_GET['file']);
  $verbose = true;

  //--------------------------------------------------------
  // HARD CODED VARIABLES
  // map csv fields to database
  // hard coded, based on NAHB13 file format
  //--------------------------------------------------------
  $field_map_csv = array(
    'Exhibitor ID' => 'importid',
    'Exhibiting As' => 'exhibitingas',
    'Booth Number' => 'booth_num',
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
    'Central' => '973',
    'North' => '972',
    'Silver Lot' => '1057'
  );


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
        $loaded[$row->importid] = array('id'=>$row->id,'menuitem_id'=>$row->menuitem_id,'exhibitingas'=>$row->exhibitingas,'allowupdate'=>$row->allowupdate,'te_id'=>$row->te_id,'te_name'=>$row->te_name);
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

    //-------------------------------------------------------------------------------------------
    //First, remove exhibitor_booths for all exhibitors in the file (* not all exhibitors for the show)
    //Loop through all the rows, get all the import ids, for each import id, match on tbl_exhibitor_show 
    //and remove all the exhibitor_booths that match
    //-------------------------------------------------------------------------------------------------
    
    $import_ids = '(';
    foreach ($csv_rows as $csv_row) {
        $import_ids .= $csv_row['importid'].',';
    }
    $import_ids = substr_replace($import_ids ,")",-1);


    $query_string = 
    "DELETE from tbl_exhibitor_booth
    WHERE id IN
    (
      SELECT tbl_exhibitor_booth.id 
      FROM tbl_exhibitor_booth
      JOIN tbl_exhibitor_show
      ON tbl_exhibitor_booth.exhibitor_show_id = tbl_exhibitor_show.id
      WHERE tbl_exhibitor_show.show_id = $show_id AND IN $import_ids
    )";
    //echo $query_string;
    //$run = mysql_query($query);
    //if($verbose) {echo "Deleted ".mysql_affected_rows ($run)." rows from tbl_exhibitor_booth.";}

    //-----------------------------------------
    // START IMPORT
    //-----------------------------------------
    
    //-----------------------------------------
    // Loop through current exhibitors with import_id
    // update  exhibitor_booths and tbl_exhibitor_show.exhibiting_as
    // if add 
    //-----------------------------------------
    foreach($loaded as $loaded_row)
    {
      print_r($loaded_row);
      echo '<br>';
    }

    // initial prep for db
    foreach ($csv_rows as $csv_row) {
      $row_count = count($csv_row);
      $query_string = '';
     foreach ($csv_row as $field => $value)
      {
        $row_count--;
        // NAHB exception - ignore descriptions
        if ($show_id==296&&$value=='2013 IBS Exhibitor') {
          $value = '';
        }
        $query_string .= $field."='".sanitize($value)."'";
        if ($row_count)
        {
          $query_string .= ', ';
        }
      }
      //echo $query_string.'<br>';

    }
    echo '<pre>';
    echo 'Header
    ';
    print_r($header);
    echo 'CSV Rows
    ';
    print_r($csv_rows);
    echo 'Loaded
    ';
    print_r($loaded);
    echo 'Done</pre>'

?>