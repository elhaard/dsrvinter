

<?php
include_once("header.php");

echo "<h2>Fix b&aring;de der har et forkert antal tilmeldte</h2>\n";

$baad_sql="SELECT * FROM `dsr_vinter_baad` ORDER BY `dsr_vinter_baad`.`type` ASC";
$baad_res=mysql_query($baad_sql, $link);
echo "<ul>\n";

while($row=mysql_fetch_row($baad_res)) {
  //For hver baad...
  $baad_ID=$row[0];
  $baad_navn=$row[1];
  $baad_type=$row[2];
  $baad_antal=$row[3];
  $baad_beskrivelse=$row[4];
  $baad_formand=$row[5];
  $baad_tilmeldte=$row[6];
  $realcount_sql="select count(*) from `dsr_vinter_person` where `baad` = $baad_ID";
  $realcount_res=@mysql_query($realcount_sql,$link);
  $realcount_row=mysql_fetch_row($realcount_res);
  $realcount=$realcount_row[0];
  echo "<li>$baad_navn: Registreret antal: $baad_tilmeldte - rigtigt antal er: $realcount";

  if ($realcount != $baad_tilmeldte){
	$sql="update dsr_vinter_baad set tilmeldte=$realcount where ID=$baad_ID";
	$_res=@mysql_query($sql,$link);
	echo " <b>FIXED</b>";
  }
  echo "\n";
 } //baadliste
  echo "</ul>\n";
    
include_once("footer.php");
?>
