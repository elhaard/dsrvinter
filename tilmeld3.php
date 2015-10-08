<?php

include_once("header.php");
?>

</head>
<body onLoad="timeFold(1)">
<script type="text/javascript"> 
<!--
function timeFold(obj) {
   var t=setTimeout("fold("+obj+")",2500);
} 

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
$email="";
$kode="";

if (isset($_POST["email"]) && isset($_POST["kode"])){
  $email=$_POST["email"];  
  $kode=$_POST["kode"];
 }

if (isset($_GET["email"]) && isset($_GET["kode"])){
  $email=$_GET["email"];
  $kode=$_GET["kode"];
 }
     
echo "</head>\n";
echo "<body>\n";

echo "<!--\n";

#var_dump($email);
#var_dump($kode);

echo "-->\n";

if (!isset($email) || !trim($email) || !isset($kode)) {
  // FEJL: email og/eller kode ikke sat
  echo "<p>Skriv din email og kode herunder</p>\n";
  echo "<form action='tilmeld3.php' method='post'>\n";
  echo "<table>\n";
  echo "<tr><td>e-mail</td><td><input type='text' name='email' value='$email'></td></tr>\n";
  echo "<tr><td>kode</td><td><input type='password' name='kode' value='$kode'></td></tr>\n";
  echo "<tr><td>&nbsp;</td><td><input type='submit' value='Log in'></td></tr>\n";
  echo "</table>\n";

 } else {
  // email og kode er sat
  $sql="select * from `dsr_vinter_person` where `email`='$email' AND `kode`='$kode' LIMIT 1";
  if (DEBUG) { echo "<br>sql: <tt>$sql</tt><br>\n";}
  $res = mysql_query($sql, $link);
  $person=mysql_fetch_assoc($res);
  $alt_ok=TRUE;

  if ($alt_ok AND $email != $person["email"]) {
    $alt_ok=FALSE;
    if (DEBUG) { echo "email ikke ens: '$email' vs '".$person["email"]."'"; }
  }
  if ($alt_ok AND $kode != $person["kode"]) {
    $alt_ok=FALSE;
    if (DEBUG) { echo "kode ikke ens: '$kode' vs '".$person["kode"]."'"; }
  }
 }


if ($alt_ok) {
  // mail og kode korrekt
  // Søg efter allerede indtastet data
  $sql="select * from `dsr_vinter_person` where `email`='$email' LIMIT 1";
  $res_person=mysql_query($sql,$link);
  $person=mysql_fetch_row($res_person);
  $navn=$person[2];
  $tlf=$person[3];
  $medlemnr=$person[1];
  $rotype=$person[5];
  $baad_valg=$person[6];
  if ($medlemnr==0) {$medlemnr=99999999;}

  if (DEBUG) {
    echo "<pre>\n";
    print_r($person);
    echo "</pre>\n";
  }
    
  $sql="select * from `dsr_vinter_rotype` order by type";
  $res_rotype=mysql_query($sql, $link);
  $sql="select * from `dsr_vinter_baad` order by navn";
  $res_baade=mysql_query($sql,$link);
  

  echo "<h2>Tilmeldingsside</h2>\n";
  echo "<p>Her tilmelder du dig det ønskede bådhold.</p><p>Du vil i starten af oktober kunne se et opslag i roklubben med alle bådhold. Ydermere vil du også høre fra bådformanden på dit hold.</p>\n";

  // Hvis der er blevet opdateret...
  if (isset($_GET["update"])) {
    echo "<div id='1' style='display:inline;'><h3>Ændringerne er gemt</div>&nbsp;</h3>\n";
  }

  echo "<form action='tilmeld4.php' method='POST'>\n";
  echo "<input type='hidden' name='kode' value='$kode'>\n";
  echo "<table>\n";
  echo "<tr><td>Navn<br>Dette felt er synligt for alle!</td><td><input type='text' name='navn' value='$navn'></td></tr>\n";
  echo "<tr><td>Medlemsnummer</td><td><input type='text' name='medlemnr' value='$medlemnr'></td></tr>\n";
  echo "<tr><td>Telefon</td><td><input type='text' name='tlf' value='$tlf'></td></tr>\n";
  echo "<tr><td>e-mail<br />(kan ikke ændres)</td><td><input type='text' name='email' value='$email' READONLY></td></tr>\n";
  echo "<tr><td>Kode</td><td><input type='password' name='kode' value='$kode'><td></tr>\n";
  echo "<tr><td>Ro type</td><td><select name='rotype'>\n";
  while ($ro_type=mysql_fetch_row($res_rotype)) {
    echo "<option value='$ro_type[0]'";
    if ($ro_type[0]==$rotype) { echo " selected"; }
    echo ">$ro_type[1]</option>\n";
  }
  echo "</select></td></tr>\n";
  echo "<tr><td>Båd <input type='hidden' name='gl_baad' value='$baad_valg'></td><td><select name='baad'>\n";
  while ($row_baad=mysql_fetch_row($res_baade)) {
    // For hver båd...
    
    if ($row_baad[3]>$row_baad[6]) {
      // Der er endnu ikke nok tilmeldte
    echo "<option value='$row_baad[0]'";
    if ($row_baad[0]==$baad_valg) { echo " selected"; }
    echo ">$row_baad[1] ($row_baad[6]/$row_baad[3])</option>\n";
    } else {
      // Der er for mange tilmeldte
      
      if ($row_baad[0]==$baad_valg) { 
	echo "<option class='optaget' value='$row_baad[0]'";
	echo " selected";
	echo ">$row_baad[1] ($row_baad[6]/$row_baad[3])</option>\n";
      }
    }
  }
  
  echo "</select><br> Tallene efter hver båd fortæller hvormange af pladserne der er optaget. Er båden rød vil du komme på en venteliste. Vælg en anden båd, hvis du vil være nogenlunde sikker på at komme på din ønskebåd.</td></tr>\n";
  echo "<tr><td>&nbsp;</td><td><input type='submit' value='Gem'></td></tr>\n";
  echo "</table>\n";
  echo "</form>\n";

 } else {
  // fejl i kode
  if ($email != '' || $kode != ''){
  echo "<h2>Ups...</h2>\n";
  echo "<p>Der er sket en fejl. Din kode var forkert. Det kan skyldes flere ting (og er formentligt ikke din skyld). Vi vil bede dig <a href='index.php'>prøve igen her</a>.</p><p>Undskyld ulejligheden</p>\n";
}
 }


  include("footer.php");
?>
