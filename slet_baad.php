<?php
header("Location: opret_baad.php");
include_once("sql.php");

if (isset($_POST["ID"])) {
  $ID=$_POST["ID"];
  $sql="DELETE FROM `dsr_vinter_baad` WHERE `dsr_vinter_baad`.`ID` = $ID LIMIT 1";
  
  //  echo "SQL: <tt>$sql</tt>\n";
  $res=mysql_query($sql,$link);
 }
  echo mysql_error();
  //  echo "<br>SQL: <tt>$sql</tt>\n";
  // die();



?>
