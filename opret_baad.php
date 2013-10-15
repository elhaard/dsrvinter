<?php
include_once("header.php");

$sql="select * from dsr_vinter_baadtype";
$boat_types_db=@mysql_query($sql,$link);
$boat_types = array("andet");
while($boat_type=mysql_fetch_row($boat_types_db)) {
  $boat_types[$boat_type[0]]=$boat_type[1];
 }
?>

</head>

<body>
<h2>Eksisterende både</h2>
<table border='1'>
  <tr><!--<td>ID</td>--><td>Navn</td><td>Type</td><td>Antal</td><td>Beskrivelse</td><td>Formand</td><td>Slet?</td></tr>

<?php

$sql="select * from  `dsr_vinter_baad`";
$boats = mysql_query($sql,$link);

while($boat=mysql_fetch_row($boats)) {
  $formand_sql="select `navn` from `dsr_vinter_person` where `medlemnr` = $boat[5]";
  $formand_res=@mysql_query($formand_sql,$link);
  $formand_row=mysql_fetch_row($formand_res);
  $formand_navn=$formand_row[0];
   echo "<tr>\n";
   //   echo "  <td>$boat[0]</td>\n";
   echo "  <td><a href='opret_baad.php?ID=$boat[0]'>$boat[1]</a></td>\n";
   $boat_type=$boat[2];
   echo "  <td>$boat_types[$boat_type]</td>\n";
   echo "  <td>$boat[3]</td>\n";
   echo "  <td>$boat[4]</td>\n";
   echo "  <td><tt>$boat[5]</tt> $formand_navn </td>\n"; //formand
   echo "  <td><form action='slet_baad.php' method='post'><input type='hidden' name='ID' value='$boat[0]'><input type='image' src='delete.gif' value='Submit' alt='Slet'></form></td>";
   echo "</tr>\n";
 }
?>

</table>

<!--------------------------------------------->

<?php
  // skal der editeres?
if(isset($_GET["ID"])) {
  $edit=TRUE;
  $ID=$_GET["ID"];
  $sql="select * from  `dsr_vinter_baad` where `ID`=$ID";
  $res = mysql_query($sql,$link);
  $valgt_baad = mysql_fetch_row($res);
//   echo "<pre>\n";
//   print_r($valgt_baad);
//   echo "</pre>\n";

  $navn=$valgt_baad[1];
  $type=$valgt_baad[2];
  $beskrivelse=$valgt_baad[4];
  $formand=$valgt_baad[5];
  $antal=$valgt_baad[3];

 } else {
  $edit=FALSE;
  $ID='';
  $navn='';
  $type=1;
  $beskrivelse=' ';
  $formand='0';
  $antal='0';
 }
?>

  <h2>(Op)ret båd</h2>
<form action="opret_baad_create.php" method='post'>
<table>
<tr><td>Båd-navn</td><td><input type='text' name='navn' value='<?php echo $navn;?>'></td></tr>
<tr><td>Type</td><td>
  <select name='type'>

<?php
foreach($boat_types as $k=>$baad_type){
  if (strcmp($type,$k)) { $selected=''; } else
    { $selected='selected'; }
  
  echo "    <option value='$k' $selected>$baad_type</option>\n";
}
?>
</select></td></tr>
<tr><td>Antal arbejdere</td><td><input type='text' name='antal' value='<?php echo $antal; ?>'></td></tr>
<tr><td>Beskrivelse</td><td><textarea name='beskrivelse' cols='20' rows='4'><?php echo $beskrivelse; ?></textarea></td></tr>
<tr><td>Formand (medlemsnummer)</td><td><input type='text' name='formand' value='<?php echo $formand;?>'></td></tr>
<tr><td colspan='2' align='right'><input type='hidden' name='ID' value='<?php echo $ID; ?>'><input type='hidden' name='edit' value='<?php echo $edit; ?>'><input type='submit' value='Gem denne båd'> <a href='opret_baad.php'>Nulstil formular</a></td></tr>

</table>
</form>

<?php

include_once("footer.php");
?>