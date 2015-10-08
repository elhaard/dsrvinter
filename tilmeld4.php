<?php

header('Content-Type: text/html; charset=utf-8');

require("sql.php");

if($_POST["medlemnr"]==0) {
  //Man må ikke have medlemsnummer 0! Det bruges til default...
  $baad_error=FALSE;
 } else {
  $sql="UPDATE `dsr_vinter_person` SET ".
    " `navn` = '".$_POST["navn"]."',".
    " `medlemnr` = ".$_POST["medlemnr"].",".
    " `tlf`='".$_POST["tlf"]."',".
    " `email`='".$_POST["email"]."',".
    " `kode`='".$_POST["kode"]."',".
    " `rotype`=".$_POST["rotype"].",".
    " `baad`=".$_POST["baad"].
    " WHERE `dsr_vinter_person`.`email` = '".$_POST["email"]."' LIMIT 1";

  mysql_query($sql,$link);
  $sql_error = mysql_error();

// opdater antal på baaden
$baad_sql="UPDATE `dsr_vinter_baad` set `tilmeldte`=`tilmeldte`+1 where `ID`='".$_POST["baad"]."'";
$baad_res=mysql_query($baad_sql,$link);
$baad_error=mysql_error();

$baad_sql="UPDATE `dsr_vinter_baad` set `tilmeldte`=`tilmeldte`-1 where `ID`='".$_POST["gl_baad"]."'";
$baad_res=mysql_query($baad_sql,$link);
$baad_error=mysql_error();
 }

  if ($sql_error || $baad_error) {
    // fejl
    include_once("header.php");
    echo "<tt>$sql_error</tt>\n<br> ";
    echo "SQL: <tt>$sql</tt>\n<br><hr>\n";
    echo "<tt>$baad_error</tt>\n<br> ";
    echo "SQL: <tt>$baad_sql</tt>\n<br><hr>\n";

    if (DEBUG) {echo "sql: <tt>$sql</tt>\n"; }
  } else {
    // fjong
    header("Location: tilmeld3.php?email=".$_POST["email"]."&kode=".$_POST["kode"]."&update");
    exit;
  }


?>

