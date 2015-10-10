

<?php
include_once("header.php");

echo "<h2>Fix b&aring;de der har et forkert antal tilmeldte</h2>\n";

$baad_sql="SELECT b.navn as baad_navn, k.navn as kategori_navn, a.* FROM dsr_vinter_baad b JOIN dsr_vinter_baad_antal a ON (a.baad = b.id) LEFT JOIN dsr_vinter_roer_kategori k ON (a.kategori = k.id) ORDER BY b.navn ASC, k.navn ASC";
$baad_res=mysql_query($baad_sql, $link);
echo "<ul>\n";

$gammel_baad = -1;

while($row=mysql_fetch_assoc($baad_res)) {
  //For hver baad/kategori

  $realcount_sql="select count(*) from `dsr_vinter_person` where `baad` = " . (int) $row['baad'] . " AND kategori = " . (int) $row['kategori'];
  $realcount_res=mysql_query($realcount_sql,$link);
  $realcount_row=mysql_fetch_row($realcount_res);
  $realcount=$realcount_row[0];
  $baad_navn = $row['baad_navn'];
  $kategori = $row['kategori_navn'];
  $baad_tilmeldte = $row['tilmeldte'];

  if ($row['baad'] != $gammel_baad) {
	if ( $gammel_baad >= 0) {
	   echo "</ul></li>\n";
        }
	echo "<li>$baad_navn <ul>\n";
  }
  $gammel_baad = $row['baad'];

  echo "<li><b>$kategori</b>: Registreret antal: $baad_tilmeldte - rigtigt antal er: $realcount";

  if ($realcount != $baad_tilmeldte){
	$sql="update dsr_vinter_baad_antal set tilmeldte=$realcount where baad = " . (int) $row['baad'] . " AND kategori = " . (int) $row['kategori'];
	$_res=@mysql_query($sql,$link);
	echo " <b>FIXED</b>";
  }
  echo "</li>\n";
 } //baadliste

if ( $gammel_baad >= 0) {
    echo "</ul></li>\n";
}

echo "</ul>\n";

include_once("footer.php");
?>
