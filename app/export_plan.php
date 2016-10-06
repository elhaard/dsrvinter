<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="vintervedligehold.csv"');
header('Content-Description: Baadhold');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once("inc/db.php");
$result = array();

if (isset($_POST["medlemsnummer"]) && isset($_POST["password"])){
  $medlemsnummer=trim($_POST["medlemsnummer"]);  
  $kode=trim($_POST["password"]);

  $res = $link->query("SELECT * FROM dsr_vinter_person WHERE ID = " . (int) $medlemsnummer . " AND kode = '" . $link->escape_string($kode) . "'");
  if ($res) {
    if ($res->num_rows == 1) {
      $user = $res->fetch_assoc();
    }
    $res->close();
  }
}

if (isset($user) && $user['is_admin']) {

  $year = get_setting('year');

  $baade = array();
  $tilmeldte = array();
  $personer = array();
  $formaend = array();
  $minimum_timer = 1;

  // Find baade
  $res = $link->query("SELECT *
                        FROM dsr_vinter_baad
                        ORDER BY navn, ID");
  if (!$res) {
     echo "Fejl: Kunne ikke finde bådliste!!!\n";
  } else {
     while ($brow = $res->fetch_assoc()) {
        $baade[] = $brow;
	$tilmeldte[ $brow['ID'] ] = array();
	$formaend[ $brow['ID'] ] = array();
     }
     $res->close();

     // Find tilmeldte
     $res = $link->query("SELECT p.*, k.timer as timer, k.navn as kategori_navn 
                          FROM dsr_vinter_person p 
                          LEFT JOIN dsr_vinter_roer_kategori k ON (p.kategori = k.ID)
                          WHERE p.baad IS NOT NULL
                          ORDER BY p.navn");
     if ($res) {
        while ($prow = $res->fetch_assoc()) {
           $personer[ $prow['ID'] ] = $prow;
	   $tilmeldte[ $prow['baad'] ][] =& $personer[ $prow['ID'] ]; 
        }
        $res->close();
     } else {
        echo "Fejl: Kunne ikke finde tilmeldte!!!\n";
     }

    
     // Find formaend
     $res = $link->query("SELECT p.*, f.baad as formandsbaad 
                          FROM dsr_vinter_person p 
                          JOIN dsr_vinter_baadformand f ON (f.formand = p.ID)
                          ");
     if ($res) {
        while ($frow = $res->fetch_assoc()) {
	   $formaend[ $frow['formandsbaad' ] ][] = $frow;
           $personer[ $frow['ID'] ]['is_formand'] = 1;
        }
        $res->close();
     } else {
        echo "Fejl: Kunne ikke finde bådformænd!!!";
     }

     echo "formand;medlemsnummer;navn;email;telefon;timer;båd;sæson\n";

     foreach( $baade as $c_baad ) {
       $baadID = $c_baad['ID'];
       $c_tilmeldte = $tilmeldte[ $baadID ];



       printf("\n%s - %s. Vurderet til %d timer\n",
              $c_baad['navn'],
              ucfirst($c_baad['periode']),
              $c_baad['max_timer']
             );

       foreach ($c_tilmeldte as $c_tilmeldt) {
	   printf("%s;%d;%s;%s;%s;%d;%s;%s\n",
                  $c_tilmeldt['is_formand'] ? 'Bådformand' : '',
                  $c_tilmeldt['ID'],
                  $c_tilmeldt['navn'],
                  $c_tilmeldt['email'],
                  $c_tilmeldt['tlf'],
                  $c_tilmeldt['timer'],
                  $c_baad['navn'],
                  $c_baad['periode']
                );
       }
    }
  }
}

?>
