<?php
header("Location: opret_baad.php");
include_once("sql.php");

if(isset($_POST["navn"])) {
  $ID=$_POST["ID"];
  $navn=htmlentities($_POST["navn"]);
  $type=htmlentities($_POST["type"]);
  $antal=htmlentities($_POST["antal"]);
  $beskrivelse=htmlentities($_POST["beskrivelse"]);
  $formand=htmlentities($_POST["formand"]);

  if ($_POST["edit"]) {
    // editér baad
    $sql="UPDATE `dsr_vinter_baad` SET ".
      "`navn` = '$navn', ".
      "`type` = $type, ".
      "`antal` = $antal, ".
      "`beskrivelse` = '$beskrivelse', ".
      "`formand` = '$formand' ".
      "WHERE `dsr_vinter_baad`.`ID` = $ID LIMIT 1";
  } else {
    // opret ny baad
    $sql="INSERT INTO `dsr_vinter_baad` (`navn` , `type` , `antal` , `beskrivelse`, `formand` ) VALUES ( '$navn', $type, $antal, '$beskrivelse', $formand )";
  }
  $res=mysql_query($sql,$link);
  
 }
  echo mysql_error();
  //  echo "<br>SQL: <tt>$sql</tt>\n";
  // die();



?>
