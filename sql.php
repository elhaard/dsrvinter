<?php

// include af denne fil medf�rer automatisk connection. Brug $link

function print_array(&$a) {
  echo "<hr><pre>";
  print_r($a);
  echo "</pre><hr>\n";
}

function sql_connect() {
  $link = mysql_connect("localhost", "tlb", "RFrdGPwbz5SzTsMe")
    or exit("Could not connect to database (from __FILE__)");
  mysql_select_db("tlb_dsrvinter2013",$link);
  return $link;
}

$asr_kode="as6Jd.9qa2V7Y"; // asr, H2SO4
$link=sql_connect();
?>
