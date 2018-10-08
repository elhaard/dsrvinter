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

  $res = $link->query("SELECT * FROM person WHERE ID = " . (int) $medlemsnummer . " AND kode = '" . $link->escape_string($kode) . "'");
  if ($res) {
    if ($res->num_rows == 1) {
      $user = $res->fetch_assoc();
    }
    $res->close();
  }
}

if (isset($user) && $user['is_admin']) {

  $year = get_setting('year');

  $teams = array();
  $tilmeldte = array();
  $personer = array();
  $formaend = array();
  $minimum_timer = 1;

  // Find teams
  $res = $link->query("SELECT t.* FROM team t,
                       GROUP_CONCAT(b.navn ORDER BY b.navn SEPARATOR '/') as boat_names
                       SUM(b.max_timer) as max_hours
                       LEFT JOIN baad b ON t.ID = b.team
                       GROUP BY t.*
                       ORDER BY boat_names, t.ID"");
  if (!$res) {
     echo "Fejl: Kunne ikke finde bådholdsliste!!!\n";
  } else {
     while ($brow = $res->fetch_assoc()) {
        $teams[] = $brow;
	      $tilmeldte[ $brow['ID'] ] = array();
	      $formaend[ $brow['ID'] ] = array();
     }
     $res->close();

     // Find tilmeldte
     $res = $link->query("SELECT p.*
                          FROM person p
                          WHERE p.team IS NOT NULL
                          ORDER BY p.navn");
     if ($res) {
        while ($prow = $res->fetch_assoc()) {
           $personer[ $prow['ID'] ] = $prow;
	         $tilmeldte[ $prow['team'] ][] = $personer[ $prow['ID'] ];
        }
        $res->close();
     } else {
        echo "Fejl: Kunne ikke finde tilmeldte!!!\n";
     }


     // Find formaend
     $res = $link->query("SELECT p.*, f.team as formandsbaad
                          FROM person p
                          JOIN baadformand f ON (f.formand = p.ID)
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

     echo "formand;medlemsnummer;navn;email;telefon;timer;bådhold;sæson\n";

     foreach( $teams as $c_team ) {
       $teamID = $c_team['ID'];
       $c_tilmeldte = $tilmeldte[ $teamID ];



       printf("\n%s - %s. Vurderet til %d timer\n",
              $c_team['boat_names'],
              ucfirst($c_team['period']),
              $c_team['max_hours']
             );

       foreach ($c_tilmeldte as $c_tilmeldt) {
	   printf("%s;%d;%s;%s;%s;%d;%s;%s\n",
                  $c_tilmeldt['is_formand'] ? 'Bådformand' : '',
                  $c_tilmeldt['ID'],
                  $c_tilmeldt['navn'],
                  $c_tilmeldt['email'],
                  $c_tilmeldt['tlf'],
                  $c_tilmeldt['hours'],
                  $c_team['boat_names'],
                  $c_team['period']
                );
       }
    }
  }
}

?>
