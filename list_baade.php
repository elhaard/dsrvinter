

<?php
include_once("header.php");
?>
<script type="text/javascript"> 
<!--
function fold(obj) {
    var el = document.getElementById(obj);
    if (el.style.display != "none" ) {
      el.style.display = 'none';
    }
    else {
      el.style.display = '';
    }
  }
//-->
</script>

<?php
echo "<h2>Oversigt over både og tilmeldinger</h2>\n";
echo "<p>Klik på bådnavnet for at se tilmeldte...</p>\n";

$baad_sql="SELECT * FROM `dsr_vinter_baad` ORDER BY `dsr_vinter_baad`.`type` ASC";
$baad_res=mysql_query($baad_sql, $link);

$count_sql="SELECT count( * ) FROM `dsr_vinter_person` WHERE `medlemnr` >0";
$count_res=mysql_fetch_row(mysql_query($count_sql,$link));
echo "<p>Der er i øjeblikket $count_res[0] tilmeldte.</p>\n";

echo "<ul>\n";

while($row=mysql_fetch_row($baad_res)) {
  //For hver baad...
  $baad_ID=$row[0];
  $baad_navn=$row[1];
  $baad_type=$row[2];
  $baad_antal=$row[3];
  $baad_beskrivelse=$row[4];
  $baad_formand=$row[5];
  $formand_sql="select `navn` from `dsr_vinter_person` where `medlemnr` = $row[5]";
  $formand_res=@mysql_query($formand_sql,$link);
  $formand_row=mysql_fetch_row($formand_res);
  $formand_navn=$formand_row[0];
  echo "<li><b><a onclick=\"fold('$baad_ID')\" style='cursor:pointer'>$baad_navn</a></b> (Formand: <b>$formand_navn</b>)\n";

  // Fold-ind/ud
  echo "    <div id='$baad_ID' style='display:none'>\n";
  echo "  <ul>\n";
  echo "    <em>Tilmeldte</em>\n";

  $tilmeldt_sql="SELECT `medlemnr` , `navn` FROM `dsr_vinter_person` WHERE `baad` =$baad_ID";
  $tilmeldt_res=mysql_query($tilmeldt_sql, $link);

  while($tilmeldt_row=mysql_fetch_row($tilmeldt_res)) {
    $tilmeldt_navn=$tilmeldt_row[1];
    $tilmeldt_medlemnr=$tilmeldt_row[0];

    if($tilmeldt_medlemnr<10) { $tilmeldt_medlemnr = "000".$tilmeldt_medlemnr; }
    if($tilmeldt_medlemnr<100) { $tilmeldt_medlemnr = "00".$tilmeldt_medlemnr; }
    if($tilmeldt_medlemnr<1000) { $tilmeldt_medlemnr = "0".$tilmeldt_medlemnr; }
    if($tilmeldt_medlemnr<10000) { $tilmeldt_medlemnr = "".$tilmeldt_medlemnr; }

    echo "      <li><tt>$tilmeldt_medlemnr</tt> $tilmeldt_navn</li>\n";
  } // tilmeldt-liste
  echo "    </ul>\n";
  echo "  </div>  <!-- Folde in/ud -->\n";
 } //baadliste
  echo "</ul>\n"; // Baadliste
    
include_once("footer.php");
?>
