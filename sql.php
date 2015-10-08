<?php

// include af denne fil medfører automatisk connection. Brug $link

function print_array(&$a) {
  echo "<hr><pre>";
  print_r($a);
  echo "</pre><hr>\n";
}

function sql_connect() {
  $link = mysql_connect("localhost", "nversion", "EPv0Jq8S0QTG72wLF9WPl3F")
    or exit("Could not connect to database (from __FILE__)");
  mysql_select_db("nversion_dsrvinter2014",$link);
  mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $link);
  return $link;
}

$asr_kode="as6Jd.9qa2V7Y"; // asr, H2SO4
$link=sql_connect();
?>
