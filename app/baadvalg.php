<?php


include("inc/header.php");
if (isset($user)) {

  $year = get_setting('year');
  echo "</head>\n<body>\n";

  echo "<h2>Vintervedligehold $year for " . $user['navn'] . "</h2>\n";


  if ($user['is_admin']) {
    ?>
    <div class="administrator-info">
      <p>Du er vintervedligeholds-administrator!</p>
      <form action="import_baade.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Importer både" />
      </form>
      <form action="admin_baade.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Administrer både" />
      </form>
      <form action="import_roere.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Importer roere" />
      </form>
      <form action="admin_roere.php" method="post">
         <?= $form_fields ?>
         <input type="submit" value="Administrer roere" />
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
  $res = $link->query("SELECT b.* FROM dsr_vinter_baad b JOIN dsr_vinter_baadformand f ON (b.ID = f.baad) WHERE f.formand = " . (int) $user['ID']);
  if ($res->num_rows == 1) {
      $formand = $res->fetch_assoc();
      $res->free();
      echo "<p><b>Du er bådformand for " . $formand['navn'] . "</b></p>";
      echo "<p>Derfor kan du ikke melde dig på andre bådhold...</p>";
  } else {
      $res->close();

      echo "<p>I år afhænger vintervedligehold af, hvor mange kilometer, man har roet.</p>";
      echo "<p>Dine roede kilometer i $year svarer til kategori <b>" . $user['kategori_navn']
           . "</b>. Det betyder, at du forventes at deltage i vintervedligehold i mindst <b>"
           . $user['kategori_timer'] . " timer</b>.</p>\n";
  }
  echo "</div>\n";

  if (isset($_POST['save_user_info']) && $_POST['save_user_info'] == '1') {
     if (isset($_POST['email']) && isset($_POST['tlf'])) {
        $email = trim($_POST['email']);
	$tlf = trim($_POST['tlf']);
	if ($email != '' && $tlf != '') {
	   $saved = $link->query("UPDATE dsr_vinter_person SET email = '"
                                 . $link->escape_string($email) . "', tlf = '"
                                 . $link->escape_string($tlf) . "' WHERE ID = "
                                 . (int) $user['ID']);
	   if (! $saved) {
                echo "<p class=\"error\">Fejl: Kunne ikke gemme dine oplysninger.</p>";
           }
        }
    }
  }

  if (isset($_POST['update_boat']) && $_POST['update_boat'] == '1' && !isset($formand)) {
     if (isset($_POST['valgt_baad'])) {
        $ny_baad = (int) trim($_POST['valgt_baad']);

	if ($ny_baad > 0) {
	   $time_res = $link->query("SELECT IFNULL(SUM(k.timer),0) FROM
                                     dsr_vinter_person p
                                     JOIN dsr_vinter_roer_kategori k ON (k.ID = p.kategori)
				     WHERE p.baad = " . (int) $ny_baad);
           if ($time_res) {
              $time_row = $time_res->fetch_array();
              if ($time_row) {
		$brugte_timer = $time_row[0];
		$ledig_res = $link->query("SELECT * from dsr_vinter_baad WHERE ID = " . (int) $ny_baad);
		if ($ledig_res) {
		   $baadinfo = $ledig_res->fetch_assoc();
		   if (isset($baadinfo)) {
			if ($user['kategori_timer'] + $brugte_timer <= $baadinfo['max_timer']) {
			   if ($link->query("UPDATE dsr_vinter_person SET baad = " . (int) $ny_baad . " WHERE ID = " . (int) $user['ID'] )) {
			      echo "<p class=\"ok\">Din tilmelding er gemt.</p>\n";
                              $user['baad'] = $ny_baad;
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


  $min_baad = 0;
  if (!$formand) {
    if ($user['baad']) {
      	$res = $link->query("SELECT * FROM dsr_vinter_baad WHERE ID = " . (int) $user['baad']);
	if ($res) {
	   $baad = $res->fetch_assoc();
	   $res->close();
        } else {
           echo "<p class=\"error\">Kunne ikke finde oplysninger om din båd</p>";
        }
    }


    if (isset($baad)) {
      ?>
      <div id="min-baadtilmelding">
         <p>Du er tilmeldt vintervedligehold på båden <b><?= $baad['navn'] ?></b>.<br/>
            Den skal vedligeholdes <b><?= $baad['periode'] ?></b></p>

         <p>Hvis du hellere vil på en anden båd, så kan du vælge en anden båd herunder.</p>
      </div>
      <?php
      $min_baad = $baad['ID'];
    } else {
      ?>
      <div id="ikke-tilmeldt">
         <p>Du er endnu ikke tilmeldt en båd. Du kan tilmelde dig herunder.</p>
      </div>
      <?php
    }
  }
  $baade = array();
  $tilmeldte = array();
  $personer = array();
  $formaend = array();

  // Find baade
  $res = $link->query("SELECT b.*, t.type as baadtype 
                        FROM dsr_vinter_baad b
                        LEFT JOIN dsr_vinter_baadtype t ON (b.type = t.ID)
                        ORDER BY b.navn, b.ID");

  if (! $res) {
     echo "<p class=\"error\">Fejl: Kunne ikke finde bådliste!!!</p>";
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
       echo "<p class=\"error\">Fejl: Kunne ikke finde tilmeldte!!!</p>";
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
       echo "<p class=\"error\">Fejl: Kunne ikke finde bådformænd!!!" . $link->error . "</p>";
    }

    echo "<h3>Bådliste</h3>\n";

    // echo "<pre>" . print_r($_POST, true) . "</pre><br/>\n";

    foreach( $baade as $c_baad ) {
      $c_tilmeldte = $tilmeldte[ $c_baad['ID'] ];
      $antal = 0;
      $timer = 0;
      foreach ($c_tilmeldte as $c_tilmeldt) {
         $antal++;
         $timer += $c_tilmeldt['timer'];
      }

      $class = '';
      $ledig = false;
      if ( $c_baad['ID'] == $min_baad ) {
           $class .= 'min_baad';
      } elseif ( $timer + $user['kategori_timer'] <= $c_baad['max_timer']) {
           $class .= ' ledig';
           $ledig = true;
      } else {
          $class .= 'optaget';
      }
      $tilmeldte_str = ($antal == 1) ? "tilmeldt" : "tilmeldte";
      echo "<div class=\"baadinfo $class\"><div class=\"baad_header\"><b>" . $c_baad['navn'] . "</b> - $antal $tilmeldte_str.";
      if ($ledig) {
          echo " Ledig.";
          if (! isset($GLOBALS['formand'])) {
              ?>
              <span class="tilmeld_span">
                  <form action="baadvalg.php" method="post">
                    <?= $form_fields ?>
		    <input type="hidden" id="vaelg-<?= $c_baad['ID'] ?>" name="valgt_baad" value="<?= $c_baad['ID'] ?>" />
		    <input type="hidden" name="update_boat" value="1" />
                    <input type="submit" value="Vælg denne båd" />
   	          </form>
              </span>
              <?php
          }
      } elseif ( $c_baad['ID'] == $min_baad ) {
          echo " Du er tilmeldt denne båd.";
      } else  {
          echo " Ingen ledige pladser.";
      }
      echo "</div>\n<div class=\"extra_info\">" . $c_baad['baadtype'];
      echo ". Vedligeholdes <b>" . $c_baad['periode'] . "</b>";
      if (isset($c_baad['beskrivelse']) && trim($c_baad['beskrivelse']) != '') {
          echo "<br/>\n<i>" . htmlspecialchars($c_baad['beskrivelse']) . "</i>";
      }
      echo "<br/>\nBådformand: ";
      $formand_count = 0;
      foreach ($formaend[ $c_baad['ID'] ] as $c_formand) {
         if ($formand_count > 0) {
            echo ", ";
         } 
         echo $c_formand['navn'];
         $formand_count++;
      }
      if ($formand_count == 0) {
         echo "<i>Ingen</i>\n";
      }

      echo "<br />\nBåden er vurderet til " . $c_baad['max_timer'] . " timer. Der er i øjeblikket tilmeldt deltagere svarende til $timer timer."; 

      echo "</div>\n";
      if ($antal > 0) {
          echo "<div class=\"baad_deltagere\">\n";
          echo "<u>Tilmeldte:</u>";
          echo "<ul class=\"deltager_liste\">\n";
	  foreach ($c_tilmeldte as $c_tilmeldt) {
             echo "<li>" . $c_tilmeldt['ID'] . ": " . $c_tilmeldt['navn'];
	     if ($c_tilmeldt['is_formand']) {
                echo " (bådformand)";
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
