<?php
include_once("header.php");

$pers_sql="SELECT `baad` , count( * ) FROM `dsr_vinter_person` WHERE `medlemnr` >0 AND `baad` >0 GROUP BY baad ORDER BY `dsr_vinter_person`.`baad` ASC";
$pers_res=mysql_query($pers_sql,$link);
//$pers_row=mysql_fetch_row($pers_res);
echo "<p>I tabellen herunder bør de <b>fede</b>kolonner være identiske. Den til venstre er hvormange der ER tilmeldt båden. Den til højre, er hvormange systemet tror der er tilmeldt. Er der en fejl, så sig til, så fixer jeg.</p>\n";
echo "<table border='1'>\n";
echo "<tr><td>Båd ID</td><td>Faktisk</td><td>Navn</td><td>Tilmeldte?</td><td>Max</td></tr>\n";
while($pers_row=mysql_fetch_row($pers_res)) {
  $baad_sql="select `ID`, `navn`,`tilmeldte`, `antal` from `dsr_vinter_baad` where `ID`=$pers_row[0] ";
  $baad_res=mysql_query($baad_sql,$link);
  $baad_row=mysql_fetch_row($baad_res);
  
  if ($baad_row[2]==$pers_row[1]) {
    $style=" style='background-color:green' ";
  } else {
    $style=" style='background-color:red' ";
  }
  echo "<tr><td>$pers_row[0]</td><td><b>$pers_row[1]</b></td>\n";
  echo "<td>$baad_row[1]</td><td $style><b>$baad_row[2]</b></td><td>$baad_row[3]</td></tr>\n";
 }
echo "</table>\n";


print_array($baad_row);
include_once("footer.php");
?>