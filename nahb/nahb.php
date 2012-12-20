<?php

  function sanitize($string,$options=array()) {

    $string = utf8_decode($string);
    $string = htmlspecialchars($string, ENT_NOQUOTES, "ISO-8859-1", 0);

    $find = array("Š","ƒ","ƒ","Ÿ","¥","Áø","©\"","Ô","&Uacute;","‡","‘","’","Á»","©\©\""," ¬C","à","'","–","n_","–","","Ê","","›","œ","¨","ª","Ž","—","É","-","Ñ","Ò","Ó","Ð","Õ","`","--","•À","Ê","å","ÊåÊ","åÊ"," ","Ëœ","Ë†","ÃŽ","Ãž","ÃŸ","Ã™","ÃŠ","Ãš","ÃŒ","Ãœ","Ãº","Âº","Ãƒ","Æ’","Ãª","Âª","Å¾","Ã¾","Ã³","Â¾","Â³","Ã²","Â²","Ã¹","Â¹","Å½","Ã½","Ã¼","Â½","Â¼","Ã‰","Ã•","Ã‡","Ã†","Ã…","Ã·","Â·","Ã¶","Â¶","Ãµ","Âµ","Ã°","Â°","Ã®","Â®","Ã¬","Â¬","Ã©","Â©","Ã§","Â§","Ã»","Â»","Ã«","Â«","Ã±","Â±","â€ž","â€™","â€š","â€œ","â€º","â€¹","â€°","â€¢","â€”","â€“","â€˜","â€¦","â€¡","Ã€","â€","Ã¥","Â¥","Ã¤","Â¤","Ã£","Â£","Ã¢","Â¢","Ã›","Ã‹","â„¢","Ã„","Ã”","Å“","Ã“","â‚¬","Ã‚","Å’","Ã’","Ã‘","Ã˜","Ã¿","Â¿","Å¸","Ã¸","Â¸","Ã´","Â´","Ã¯","Â¯","Ã¨","Â¨","Ã¦","Â¦","Å¡","Ã¡","Â¡","Ãˆ","Ã—","Ã–","Å","Ã­","Â­","tbd",".","<br>","<b>","</b>","  ",chr(145),chr(146),chr(147),chr(148),chr(151),chr(150),chr (133),chr(149),"\r","\n");

    $replace = array("&#228;","&#201;","&#201;","&#252;","&#183;","&#39;"," ","&#39;","&#218;","&#225;","'","'"," "," ","","&#225;","&#39;","&#241;","&#241;","&#241;"," "," ","&#231;","&#245;","&#250;","&reg;","&trade;","&#233;","o","...","-","-","","","-","'","'","","","","","","","","~","ˆ","Î","Þ","ß","Ù","Ê","Ú","Ì","Ü","ú","º","Ã","ƒ","ê","ª","ž","þ","ó","¾","³","ò","²","ù","¹","Ž","ý","ü","½","¼","É","Õ","Ç","Æ","Å","÷","·","ö","¶","õ","µ","ð","°","î","®","ì","¬","é","©","ç","§","û","»","ë","«","ñ","±","„","'","‚","“","›","‹","‰","•","—","–","'","…","‡","À","”","å","¥","ä","¤","ã","£","â","¢","Û","Ë","™","Ä","Ô","œ","Ó","€","Â","Œ","Ò","Ñ","Ø","ÿ","¿","Ÿ","ø","¸","ô","´","ï","¯","è","¨","æ","¦","š","á","¡","È","×","Ö","Š","í","­","",".","<br />","<strong>","</strong>"," ","'", "'", '"', '"', '-','-','...',"&bull;",'<br />','<br />');

    $string = str_replace($find,$replace,$string);

    $string = nl2br($string);

    return $string;
  }

  // db setup
  $mysql_username = 'root';
  $mysql_host   = 'localhost';
  $mysql_password = '';
  $mysql_database = 'localAdmin';

  // db connect
  $connect = mysql_connect($mysql_host, $mysql_username, $mysql_password) or die ("Could not connect: " . mysql_error());
  mysql_select_db($mysql_database,$connect);
  
  // set variables -- $show_id and $filename passed via ?id=&file=
  $show_id = mysql_real_escape_string($_GET['id']);
  $filename = mysql_real_escape_string($_GET['file']);

  // map csv fields to database
    // hard coded, based on NAHB13 file format
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
        $query_string .= $field_map_csv[$field]."='".sanitize($value)."'";
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