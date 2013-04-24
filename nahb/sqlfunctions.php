<?php

function csvRowUpdate($csv_row,$show_id)
{
  $output = "UPDATE 
            tbl_exhibitor_show as tes, tbl_exhibitor_contact as tec
            SET tes.exhibitingas = '".sanitize($csv_row['exhibitingas']).
            "', tes.description = '".sanitize($csv_row['description']).
            "', tes.www = '".  sanitize($csv_row['www']).
            "', tes.lastupdate = now()
              , tec.firstname = '".sanitize($csv_row['firstname']).
            "', tec.address1 = '".sanitize($csv_row['address1']).
            "', tec.address2 = '".sanitize($csv_row['address2']).
            "', tec.city = '".sanitize($csv_row['city']).
            "', tec.state = '".sanitize($csv_row['state']).
            "', tec.country = '".sanitize($csv_row['country']).
            "', tec.zip = '".sanitize($csv_row['country']).
            "', tec.phone = '".sanitize($csv_row['phone']).
            "', tec.fax = '".sanitize($csv_row['fax']).
            "', tec.email = '".sanitize($csv_row['email']).    
            "' WHERE tes.importid = '".$csv_row['importid']."'
            AND tec.exhibitor_show_id = tes.id
            AND tec.contacttype = 'directory'
            AND tes.show_id = $show_id
            ";
    return $output;
}

function csvRowNoUpdate($csv_row,$show_id)
{
	$output =  "UPDATE 
            tbl_exhibitor_show as tes
            SET tes.exhibitingas = '".sanitize($csv_row['exhibitingas']).
            "WHERE tes.importid = ".$csv_row['importid']."
            tbl_exhibitor_show.show_id = $show_id
            ";
    return $output;
}

function csvRowBoothInsert($csv_row,$tesid,$map_handling)
{
	$output = "INSERT into tbl_exhibitor_booth
              (
              exhibitor_show_id,
              map_id,
              boothnum,
              updated_at
              ) 
            values(
              "  .$tesid.
              ",".$map_handling[$csv_row['building']].
              ",'".sanitize($csv_row['booth_num']).
              "',now()
              )
              ";
     return $output;

 }

function deleteBooths($show_id,$import_ids)
{
  //echo $import_ids.'<br>';
  $output= "DELETE from tbl_exhibitor_booth WHERE tbl_exhibitor_booth.id IN 
  (select booth_id from ( SELECT b.id as booth_id FROM tbl_exhibitor_booth as b
  LEFT JOIN tbl_exhibitor_show as a ON b.exhibitor_show_id = a.id 
  WHERE a.show_id = $show_id 
  AND a.importid IN $import_ids) as c)";
  
	return $output;
}

function createExhibitorwithID($rowid,$show_id,$menuitem_id,$csv_row)
{
  $insert_query_string = "Insert into tbl_exhibitor_show
              (
                show_id,
                menuitem_id,
                exhibitor_id,
                importid
              )
              values
              (
                '$show_id',
                '$menuitem_id',
                '".$rowid."',
                '".$csv_row['importid']."'
              )";
              $run = mysql_query($insert_query_string);
              $tesid = mysql_insert_id();
              $insert_query_string = "INSERT into tbl_exhibitor_contact
              (
                exhibitor_id,
                exhibitor_show_id,
                contacttype
                )
              values
              (
                '".$rowid."',
                '".$tesid."',
                'directory'
                )
              ";
              //echo $insert_query_string."s <br>";
               $run = mysql_query($insert_query_string);
             return $tesid;
}

function createExhibitorNoID($show_id,$menuitem_id,$csv_row)
{

  // echo "createExhibitorwithNOID ".$show_id.' '.$menuitem_id."<br>";
  // echo "<pre>";
  // print_r($csv_row);
  // echo "</pre>";
	$insert_query_string = "Insert into tbl_exhibitors (name,dateadded) VALUES ('". sanitize($csv_row['exhibitingas'])."',now())";
	// echo $insert_query_string."<br>";
  $run = mysql_query($insert_query_string);
    $rowid = mysql_insert_id();
	$insert_query_string = "Insert into tbl_exhibitor_show
              (
                show_id,
                menuitem_id,
                exhibitor_id,
                importid
              )
              values
              (
                '$show_id',
                '$menuitem_id',
                '".$rowid."',
                '".$csv_row['importid']."'
              )";
              $run = mysql_query($insert_query_string);
              $tesid = mysql_insert_id();
              $insert_query_string = "INSERT into tbl_exhibitor_contact
              (
                exhibitor_id,
                exhibitor_show_id,
                contacttype
                )
              values
              (
                '".$rowid."',
                '".$tesid."',
                'directory'
                )
              ";
              $run = mysql_query($insert_query_string);
              return $tesid;
}



?>