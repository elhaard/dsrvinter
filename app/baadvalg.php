<?php


include("inc/header.php");
$is_foreman = false;
if (isset($user)) {

  $year = get_setting('year');
  $booking_factor = ((int) get_setting('booking_percentage')) / 100;
  echo "</head>\n<body>\n";

  echo "<h2>Vintervedligehold $year for " . $user['navn'] . "</h2>\n";


  if ($user['is_admin']) {
  	$booking_info = '';
  	if ($booking_factor != 1) {
  		$booking_info = "Bådene kan lige nu bookes med <b>" . get_setting('booking_percentage') . "%</b>.";
  	}
    ?>
    <div class="administrator-info">
      <p>Du er vintervedligeholds-administrator! <?= $booking_info ?></p>
      <form action="import_baade.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Importer både" />
      </form>
      <form action="admin_baade.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Administrer både" />
      </form>
      <form action="admin_roere.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Administrer roere" />
      </form>
      <form action="admin_settings.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Indstillinger" />
      </form>

      <form action="export_plan.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Eksporter" />
      </form>
      <form action="send_mails.php" method="post" onsubmit="return confirm('Vil du sende invitationer til alle, der ikke allerede har fået invitation?')">
         <?= $form_fields ?>
         <input type="submit" value="Send invitationer" />
      </form>
      <form action="baadvalg.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Genindlæs denne side" />
      </form>
    </div>
    <?php
  }

  echo '<div id="roer-info">';
  // Find formands-oplysninger
  $res = $link->query("SELECT b.* FROM baad b JOIN team t ON b.team = t.ID JOIN baadformand f ON (t.ID = f.team) WHERE f.formand = " . (int) $user['ID']);

  if ($res->num_rows >= 1) {
      $baade = [];
      while ($formand = $res->fetch_assoc()) {
        $is_foreman = true;
        $baade[] = $formand.navn;
      }
      $res->free();
      echo "<p><b>Du er bådformand for " . join("/", $baade) . "</b></p>";
      echo "<p>Derfor kan du ikke melde dig på andre bådhold...</p>";
  } else {
      $res->close();

      echo "<p>Omfanget af vintervedligehold afhænger af, hvor mange kilometer, man har roet.</p>";
      echo "<p>Dine ";
      if (isset($user['km']) && $user['km'] > 0) {
         echo "<b>" . $user['km'] . "</b> ";
      }
      echo "roede kilometer svarer til, at du forventes at deltage i vintervedligehold i mindst <b>"
           . $user['hours'] . " timer</b>\n</p>\n";
  }
  echo "</div>\n";

  if (isset($_POST['save_user_info']) && $_POST['save_user_info'] == '1') {
     if (isset($_POST['email']) && isset($_POST['tlf'])) {
        $email = trim($_POST['email']);
	$tlf = trim($_POST['tlf']);
	if ($email != '' && $tlf != '') {
	   $saved = $link->query("UPDATE person SET email = '"
                                 . $link->escape_string($email) . "', tlf = '"
                                 . $link->escape_string($tlf) . "' WHERE ID = "
                                 . (int) $user['ID']);
	   if (! $saved) {
                echo "<p class=\"error\">Fejl: Kunne ikke gemme dine oplysninger.</p>";
           }
        }
    }
  }

  if (isset($_POST['update_team']) && $_POST['update_team'] == '1' && !$is_foreman) {
     if (isset($_POST['chosen_team'])) {
        $new_team = (int) trim($_POST['chosen_team']);

	if ($new_team > 0) {
	   $time_res = $link->query("SELECT IFNULL(SUM(p.hours),0) FROM
                                     person p
				     WHERE p.team = " . (int) $new_team);
           if ($time_res) {
              $time_row = $time_res->fetch_array();
              if ($time_row) {
		$brugte_timer = $time_row[0];
		$ledig_res = $link->query("SELECT SUM(max_timer) as max_timer, GROUP_CONCAT(navn ORDER BY navn SEPARATOR '/') from baad WHERE team = " . (int) $new_team);
		if ($ledig_res) {
		  $baadinfo = $ledig_res->fetch_assoc();
		  if (isset($baadinfo)) {
			 if ($user['hours'] + $brugte_timer <= $booking_factor * $baadinfo['max_timer']) {
			   if ($link->query("UPDATE person SET wished_team = " . (int) $new_team . ", team = " . (int) $new_team . " WHERE ID = " . (int) $user['ID'] )) {
			      echo "<p class=\"ok\">Din tilmelding er gemt.</p>\n";
            $user['team'] = $new_team;
         } else {
			      echo "<p class=\"error\">Fejl: Ændringerne kunne ikke gemmes.</p>\n";
         }
       } else {
			   echo "<p class=\"error\">Der er ikke flere ledige pladser på <b>" . $baadinfo['navn'] . "</b></p>\n";
       }
      }
      $ledig_res->close();
    }
  }
  $time_res->close();
  }
  }
  }
}


  $mit_team = 0;
  if ($user['team']) {
    $res = $link->query("SELECT t.ID as ID, t.name as name, GROUP_CONCAT(b.navn ORDER BY b.navn SEPARATOR '/') as boatnames FROM team t LEFT JOIN baad b ON b.team = t.ID WHERE t.ID = " . ((int) $user['team']) . " GROUP BY t.ID, t.name");
      if ($res) {
        $team = $res->fetch_assoc();
	      $res->close();
      } else {
         echo "<p class=\"error\">Kunne ikke finde oplysninger om dit bådhold</p>";
      }

    if (isset($team)) {
      $mit_team = $team['ID'];

      if (!$is_foreman) {
      ?>
      <div id="min-baadtilmelding">
         <p>Du er tilmeldt vintervedligehold på bådholdet <b><?= ($team['name'] || $team['boatnames']) ?></b>.<br/>
            Den skal vedligeholdes <b><?= $team['period'] ?></b></p>

         <p>Hvis du hellere vil på et andet bådhold, så kan du vælge et herunder.</p>
      </div>
      <?php
      }
    } else if (!$is_foreman) {
      ?>
      <div id="ikke-tilmeldt">
         <p>Du er endnu ikke tilmeldt et bådhold. Du kan tilmelde dig herunder.</p>
      </div>
      <?php
    }
  }
  $teams = array();
  $teamIndexes = array();
  $tilmeldte = array();
  $personer = array();
  $formaend = array();

  // Find teams
  $res = $link->query("SELECT * FROM team ORDER BY name, ID");
  if (! $res) {
     echo "<p class=\"error\">Fejl: Kunne ikke finde bådholdliste!!!</p>";
  } else {
    while ($t_row = $res->fetch_assoc()) {
      $teamIndexes[$t_row['ID']] = count($teams);
      $t_row['boats'] = [];
      $t_row['max_hours'] = 0;
      $t_row['boat_names'] = [];
      $teams[] = $t_row;
      $tilmeldte[ $t_row['ID'] ] = array();
	    $formaend[ $t_row['ID'] ] = array();
    }

    // Find boats
    $res = $link->query("SELECT b.*, t.type as baadtype
                         FROM baad b
                         LEFT JOIN baadtype t ON (b.type = t.ID)
                         ORDER BY b.navn, b.ID");

    if ($res) {
      while ($b = $res->fetch_assoc()) {
        $teams[$teamIndexes[$b['team']]]['boats'][] = $b;
        $teams[$teamIndexes[$b['team']]]['boat_names'][] = $b['navn'];
        $teams[$teamIndexes[$b['team']]]['max_hours'] += $b['max_timer'];
      }
      $res->close();
    } else {
      echo "<p class=\"error\">Fejl: Kunne ikke finde både!!!</p>";
    }

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
       echo "<p class=\"error\">Fejl: Kunne ikke finde tilmeldte!!!</p>";
    }

    // Find formaend
    $res = $link->query("SELECT p.*, f.team as formandsteam
                          FROM person p
                          JOIN baadformand f ON (f.formand = p.ID)
                          ");
    if ($res) {
        while ($frow = $res->fetch_assoc()) {
	        $formaend[ $frow['formandsteam' ] ][] = $frow;
          $personer[ $frow['ID'] ]['is_formand'] = 1;
        }
        $res->close();
    } else {
       echo "<p class=\"error\">Fejl: Kunne ikke finde bådformænd!!!" . $link->error . "</p>";
    }

    echo "<h3>Bådholdsliste</h3>\n";

    // echo "<pre>" . print_r($_POST, true) . "</pre><br/>\n";

    foreach( $teams as $c_team ) {
      $hidden_class='';
      $hidden_text='';
      if ($c_team['hidden']) {
      	 if ($user['is_admin']) {
	          $hidden_class="hidden_boat ";
	          $hidden_text=' (skjult båd)';
	        } else {
      	    continue;
	        }
      }
      $c_tilmeldte = $tilmeldte[ $c_team['ID'] ];
      $antal = 0;
      $timer = 0;
      foreach ($c_tilmeldte as $c_tilmeldt) {
         $antal++;
         $timer += $c_tilmeldt['hours'];
      }

      $class = $hidden_class;
      $ledig = false;
      if ( $c_team['ID'] == $mit_team ) {
           $class .= ' min_baad';
      } elseif ( $timer + $user['hours'] <= $booking_factor * $c_team['max_hours']) {
           $class .= ' ledig';
           $ledig = true;
      } else {
          $class .= ' optaget';
      }
      $tilmeldte_str = ($antal == 1) ? "tilmeldt" : "tilmeldte";
      echo "<div class=\"baadinfo $class\"><div class=\"team_header\"><b>" . ($c_team['name'] || join('/', $c_team['boat_names'])) . $hidden_text . "</b> - $antal $tilmeldte_str.";
      if ($ledig) {
          echo " Ledig.";
          if (! $GLOBALS['is_formand']) {
              ?>
              <span class="tilmeld_span">
                  <form action="baadvalg.php" method="post">
                    <?= $form_fields ?>
		    <input type="hidden" id="vaelg-<?= $c_team['ID'] ?>" name="chosen_team" value="<?= $c_team['ID'] ?>" />
		    <input type="hidden" name="update_team" value="1" />
                    <input type="submit" value="Vælg denne båd" />
   	          </form>
              </span>
              <?php
          }
      } elseif ( $c_team['ID'] == $mit_team ) {
          echo " Du er tilmeldt dette hold.";
      } else  {
          echo " Ingen ledige pladser";
          if ($booking_factor < 1)  {
          	echo " i øjeblikket";
          }
          echo ".";
      }
      echo "Vedligeholdes <b>" . $c_team['periode'] . "</b>";
      echo "<br/>\nBådformand: ";
      $formand_count = 0;
      foreach ($formaend[ $c_team['ID'] ] as $c_formand) {
         if ($formand_count > 0) {
            echo ", ";
         }
         echo $c_formand['navn'];
         $formand_count++;
      }
      if ($formand_count == 0) {
         echo "<i>Ingen</i>\n";
      }
      $boats_str = count($c_team['boats']) == 1 ? 'Båden' : 'Bådene';
      echo "<br />\n" . $boats_str . " er vurderet til i alt " . $c_team['max_hours'] . " timer.";
      if ($booking_factor != 1) {
      	echo " Lige nu er der åbent for tilmelding af op til " . ($booking_factor * $c_team['max_hours']) . " timer.";
      }
      echo " Der er i øjeblikket tilmeldt deltagere svarende til $timer timer.";
      echo "</div>\n";



      foreach ($c_team['boats'] as $c_boat) {
         echo "<div class=\"boat_description\"><b>" . $c_boat['navn'] . "</b> - <i>" . $c_boat['baadtype'] . "</i> (" . $c_boat['max_timer'] . " timer)<br/>\n";
         if (isset($c_boat['beskrivelse']) && trim($c_boat['beskrivelse']) != '') {
           echo "<div class=\"baadbeskrivelse\">" . nl2br(htmlspecialchars($c_boat['beskrivelse'])) . "</div>";
         }
         echo "</div>\n";
      }

      if ($antal > 0) {
          echo "<div class=\"baad_deltagere\">\n";
          echo "<u>Tilmeldte:</u>";
          echo "<ul class=\"deltager_liste\">\n";
	        foreach ($c_tilmeldte as $c_tilmeldt) {
             echo "<li>" . $c_tilmeldt['ID'] . ": " . $c_tilmeldt['navn'];
	           if (isset($c_tilmeldt['is_formand']) && $c_tilmeldt['is_formand']) {
                echo " (bådformand)";
             }
	           if ($user['is_admin'] && (!isset($c_tilmeldt['wished_team']) or $c_tilmeldt['wished_team'] != $c_team['ID'])) {
	     	       echo " (tvangstilmeldt)";
	           }
             echo "</li>\n";
          }
          echo "</ul>\n</div>";
      }
      echo "</div>\n\n";
    }
  }
}
include("inc/footer.php");
?>
