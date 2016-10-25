<?php


include("inc/header.php");
if (isset($user) && $user['is_admin']) {

  $year = get_setting('year');
  echo "</head>\n<body>\n";

  echo "<h2>Administer både</h2>\n";

  echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";


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
	}
	
	
	if ($action == 'delete_boat') {
	  if (isset($_POST['boatID']) && $boatID = (int) $_POST['boatID']) {
		$res = $link->query("DELETE FROM baad WHERE ID =" . $boatID . " AND ID NOT IN (SELECT baad FROM person)");
		if ($res) {
		  echo "<p class=\"error\">Kunne ikke slette båd</p>";
		  error_log("Could not delete boat: " . $link->error);
		}
	  }
	} else if ($action == 'edit_boat') {
	  if (isset($_POST['boatID']) && $boatID = (int) $_POST['boatID']) {
		$sth = $link->prepare("UPDATE baad SET navn = ?, type = ?, periode = ?, max_timer = ?, beskrivelse = ? WHERE ID = ?");
		if ($sth) {
		  $sth->bind_param("sisisi",
		  		            $_POST['name'],
		  					$newTypeID ? $newTypeID : $_POST['type'],
		  					$_POST['period'],
		  					$_POST['hours'],
		  					$_POST['description'],
		  		            $boatID
		  		);
		  $res = $sth->execute();
		}
		if (!$res) {
		  echo "<p class=\"error\">Kunne ikke gemme båd</p>";
		  error_log("Could not update boat: " . $link->error);
		}
	  }
	} else if ($action == 'new_boat') {
	   $sth = $link->prepare("INSERT INTO baad (navn, type, periode, max_timer, beskrivelse) VALUES (?,?,?,?,?)");
	   if ($sth) {
		 $sth->bind_param("sisis",
		  		            $_POST['name'],
		  					$newTypeID ? $newTypeID : $_POST['type'],
		  					$_POST['period'],
		  					$_POST['hours'],
		  					$_POST['description']
		  		);
         $res = $sth->execute();
	   }
	   if (!$res) {
	     echo "<p class=\"error\">Kunne ikke oprette båd</p>";
	     error_log("Could not insert boat: " . $link->error);
	   }
	}
  }

  $baade = array();
  $tilmeldte = array();
  $personer = array();
  $formaend = array();
  $minimum_timer = 1;

  // Find baade
  $res = $link->query("SELECT b.*, t.type as baadtype 
                        FROM baad b
                        LEFT JOIN baadtype t ON (b.type = t.ID)
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

    // Find minimum timetal
    $res = $link->query("SELECT timer FROM roer_kategori
                         WHERE timer IS NOT NULL AND timer > 0
                         ORDER BY timer ASC
                         LIMIT 1");
    if ($res) {
        $t = $res->fetch_array();
        if ($t) {
            $minimum_timer = $t[0];
        }
    } else {
       echo "<p class=\"error\">Fejl: Kunne ikke finde minimalt timetal!!!</p>";
    }

    // Find tilmeldte
    $res = $link->query("SELECT p.*, k.timer as timer, k.navn as kategori_navn 
                          FROM person p 
                          LEFT JOIN roer_kategori k ON (p.kategori = k.ID)
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
    
    <h3>Bådliste</h3>
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

    foreach( $baade as $c_baad ) {
      $baadID = $c_baad['ID'];
      $c_tilmeldte = $tilmeldte[ $baadID ];
      $antal = 0;
      $timer = 0;
      foreach ($c_tilmeldte as $c_tilmeldt) {
         $antal++;
         $timer += $c_tilmeldt['timer'];
      }

      $total_tilmeldt_antal += $antal;
      $total_tilmeldt_timer += $timer;
      $total_max_timer += $c_baad['max_timer'];
      
      $class = '';
      $ledig = false;
      $status = '';
      if ( $timer + $minimum_timer <= $c_baad['max_timer']) {
           $class .= ' ledig';
           $status = 'Ledig';
           $ledig = true;
      } else {
          $class .= ' optaget';
          $status = 'Optaget';
	  if ($timer >= $c_baad['max_timer'] + 3 ) {
             $class .= ' overfyldt';
	  }
      }
      
      ?>

        <tr class="baad_raekke <?=$class?>">
           <td><?=$c_baad['navn']?></td>
           <td><?=$c_baad['periode']?></td>
           <td><?=$status?></td>
           <td><?=$antal?></td>
           <td><?=$timer?></td>
           <td><?=$c_baad['max_timer']?></td>
           <td><form class="table-button-form" action="edit_boat.php" method="POST">
                 <?=$form_fields?>
                 <input type="hidden" name="action" value="edit_boat" />
                 <input type="hidden" name="boatID" value="<?=$c_baad['ID']?>" />
                 <input type="submit" value="Rediger" />
               </form>
           </td>

           <td><form class="table-button-form" action="admin_baade.php" method="POST" onsubmit="return confirm('Er du sikker på, at du vil slette <?= $c_baad['navn'] ?>?')" >
                 <?=$form_fields?>
                 <input type="hidden" name="action" value="delete_boat" />
                 <input type="hidden" name="boatID" value="<?=$c_baad['ID']?>" />
                 <input type="submit" value="Slet" <?= ($antal > 0) ? 'disabled="disabled" title="Båden kan ikke slettes, da der er tilmeldte roere."' : ''?>/>
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
