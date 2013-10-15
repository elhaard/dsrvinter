<?php

// include af denne fil medfører automatisk connection. Brug $link

function print_array(&$a) {
  echo "<hr><pre>";
  print_r($a);
  echo "</pre><hr>\n";
}

function sql_connect() {
  $link = mysql_connect("localhost", "dsrvinter", "6W2nAsT4S8tV")
    or exit("Could not connect to database (from __FILE__)");
  mysql_select_db("dsrvinter2013",$link);
  return $link;
}

$asr_kode="as6Jd.9qa2V7Y"; // asr, H2SO4
$link=sql_connect();
?>
