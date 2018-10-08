<?php


include("inc/header.php");
if (isset($user) && $user['is_admin']) {

  $year = get_setting('year');
  echo "</head>\n<body>\n";

  echo "<h2>Administer både</h2>\n";

  echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";

  $team = isset($_POST['team'] && $_POST['team']) ? $_POST['team'] : 0;
  if (isset($_POST['action'])) {
	$action = $_POST['action'];
	if ( isset($_POST['new_type']) && ($action == 'edit_boat' || $action == 'new_boat')) {
		$newType = trim($_POST['new_type']);
		if ($newType) {
			$sth = $link->prepare('INSERT INTO baadtype (type) VALUES (?)');
			if ($sth) {
				$sth->bind_param("s", $newType);
				$sth->execute();
				$newTypeID = $link->insert_id;
			}
		}
    if ($team == 'new') {
      $res = $link->query("INSERT INTO team");
      if ($res) {
        $team = $link->insert_id;
      }
    }
	}


	if ($action == 'delete_boat') {
	  if (isset($_POST['boatID']) && $boatID = (int) $_POST['boatID']) {
		$res = $link->query("DELETE FROM baad WHERE ID =" . $boatID . " AND team NOT IN (SELECT team FROM person)");
  		if ($res) {
	  	  echo "<p class=\"error\">Kunne ikke slette båd</p>";
		    error_log("Could not delete boat: " . $link->error);
		  }
	  }
	} else if ($action == 'edit_boat') {
	  if (isset($_POST['boatID']) && $boatID = (int) $_POST['boatID'])
  		$sth = $link->prepare("UPDATE baad SET navn = ?, type = ?, periode = ?, max_timer = ?, beskrivelse = ?, team = ? WHERE ID = ?");
      $res = '';
		  if ($sth) {
		    $hidden = isset($_POST['hidden']) && $_POST['hidden'] == 1 ? 1 : 0;
		    $sth->bind_param("sisisii",
		  	                 $_POST['name'],
		  					         $newTypeID ? $newTypeID : $_POST['type'],
		  					         $_POST['period'],
		  					         $_POST['hours'],
		  		               $_POST['description'],
							           $team,
		  		               $boatID
		  		);
		    $res = $sth->execute();
		  }
		  if ($res) {
        $link->query("UPDATE team set hidden = " . $hidden . " where ID = " . team");
      } else {
		    echo "<p class=\"error\">Kunne ikke gemme båd</p>";
		    error_log("Could not update boat: " . $link->error);
		  }
	  }
	} else if ($action == 'new_boat') {
	   $sth = $link->prepare("INSERT INTO baad (navn, type, periode, max_timer, beskrivelse, team) VALUES (?,?,?,?,?,?)");
     $res = '';
	   if ($sth) {
		 $sth->bind_param("sisisi",
		  		            $_POST['name'],
		  					      $newTypeID ? $newTypeID : $_POST['type'],
		  					      $_POST['period'],
		  					      $_POST['hours'],
		  					      $_POST['description'],
                      $team
		  		);
         $res = $sth->execute();
	   }
	   if ($res) {
       $link->query("UPDATE team set hidden = " . $hidden . " where ID = " . team");
     } else {
	     echo "<p class=\"error\">Kunne ikke oprette båd</p>";
	     error_log("Could not insert boat: " . $link->error);
	   }
	}
  }

  $teams = array();
  $teamIndexes = array();
  $tilmeldte = array();
  $personer = array();
  $formaend = array();
  $minimum_timer = 1;

  // Find teams
  $res = $link->query("SELECT t.*,
                              GROUP_CONCAT(b.navn ORDER BY b.navn SEPARATOR '/') as boat_names,
                              SUM(b.max_timer) as max_hours
                       FROM team t
                       LEFT JOIN baad b ON b.team = t.ID
                       GROUP BY team.ID
                       ORDER BY boat_names");
  if (! $res) {
    echo "<p class=\"error\">Fejl: Kunne ikke finde bådholdsliste!!!</p>";
  } else {
    while ($row = $res->fetch_assoc()) {
      $row['boats'] = [];
      $tilmeldte[ $row['ID'] ] = [];
    	$formaend[ $row['ID'] ] = [];
      $teamIndexes[$row['ID']] = count($teams);
      $teams[] = $row;
    }
    $res->close();

    $res = $link->query("SELECT b.*, t.type as baadtype
                        FROM baad b
                        LEFT JOIN baadtype bt ON (b.type = bt.ID)
                        ORDER BY b.navn, b.ID");

    if (! $res) {
      echo "<p class=\"error\">Fejl: Kunne ikke finde bådliste!!!</p>";
    } else {
      while ($brow = $res->fetch_assoc()) {
        $team = $brow['team'];
        $teams[$teamIndexes[$team]]['boats'][] = $brow;
      }
      $res->close();
    }

    // Find minimum timetal
    $res = $link->query("SELECT MIN(hours) as timer FROM person
                         WHERE hours > 0");
    if ($res) {
        $t = $res->fetch_array();
        if ($t) {
            $minimum_timer = $t[0];
        }
    } else {
       echo "<p class=\"error\">Fejl: Kunne ikke finde minimalt timetal!!!</p>";
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

    $total_tilmeldt_antal = 0;
    $total_tilmeldt_timer = 0;
    $total_max_timer = 0;

    ?>

    <form class="table-button-form" action="edit_boat.php" method="POST">
        <?=$form_fields?>
        <input type="hidden" name="action" value="new_boat" />
        <input type="submit" value="Opret ny båd" />
    </form>

    <h3>Bådholdliste</h3>
    <table class="baad_tabel">
      <tr>
        <th>Navn</th>
        <th>Sæson</th>
        <th>Status</th>
        <th>Antal tilmeldte</th>
        <th>Timer tilmeldt</th>
        <th>Max timer</th>
        <td colspan="2">&nbsp;</td>
     </tr>
    <?php

    foreach( $teams as $c_team ) {
      $teamID = $c_team['ID'];
      $c_tilmeldte = $tilmeldte[ $teamID ];
      $antal = 0;
      $timer = 0;
      foreach ($c_tilmeldte as $c_tilmeldt) {
         $antal++;
         $timer += $c_tilmeldt['hours'];
      }

      $total_tilmeldt_antal += $antal;
      $total_tilmeldt_timer += $timer;
      $total_max_timer += $c_team['max_hours'];

      $class = '';
      $ledig = false;
      $status = '';
      $boat_count = count($c_team['boats']);
      if ( $timer + $minimum_timer <= $c_team['max_hours']) {
           $class .= ' ledig';
           $status = 'Ledig';
           $ledig = true;
      } else {
          $class .= ' optaget';
          $status = 'Optaget';
	  if ($timer >= $c_team['max_hours'] + 3 ) {
             $class .= ' overfyldt';
	  }
      }
    for ($boatIndex = 0; $boatIndex < $boat_count; $boatIndex++) {
      ?>

        <tr class="baad_raekke <?=$class?>">
           <td><?=$c_team['boats'][$boatIndex]['navn']?></td>
       <?php
         if ($boatIndex == 0) {
        ?>
           <td rowspan="<?= $boat_count ?>" valign="top"><?=$c_team['period']?></td>
           <td rowspan="<?= $boat_count ?>" valign="top"><?=$status?></td>
           <td rowspan="<?= $boat_count ?>" valign="top"><?=$antal?></td>
           <td rowspan="<?= $boat_count ?>" valign="top"><?=$timer?></td>
           <td rowspan="<?= $boat_count ?>" valign="top"><?=$c_team['max_hours']?></td>
      <?php } ?>
           <td><form class="table-button-form" action="edit_boat.php" method="POST">
                 <?=$form_fields?>
                 <input type="hidden" name="action" value="edit_boat" />
                 <input type="hidden" name="boatID" value="<?=$c_team['boats'][$boatIndex]['ID']?>" />
                 <input type="submit" value="Rediger" />
               </form>
           </td>

           <td><form class="table-button-form" action="admin_baade.php" method="POST" onsubmit="return confirm('Er du sikker på, at du vil slette <?= $c_team['boats'][$boatIndex]['navn'] ?>?')" >
                 <?=$form_fields?>
                 <input type="hidden" name="action" value="delete_boat" />
                 <input type="hidden" name="boatID" value="<?=$c_team['boats'][$boatIndex]['ID']?>" />
                 <input type="submit" value="Slet" <?= ($antal > 0 && $boat_count == 1) ? 'disabled="disabled" title="Båden kan ikke slettes, da der er tilmeldte roere."' : ''?>/>
               </form>
           </td>
        </tr>
     <?php

    }

    ?>
      <tr class="total-row">
        <td>Ialt</td>
        <td></td>
        <td></td>
        <td><?=$total_tilmeldt_antal?></td>
        <td><?=$total_tilmeldt_timer?></td>
        <td><?=$total_max_timer?></td>
        <td colspan="2">
     </tr>
   </table>
   <?php

  }
}

echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";
include("inc/footer.php");
?>
