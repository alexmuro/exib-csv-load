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
?>