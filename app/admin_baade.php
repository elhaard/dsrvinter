<?php


include("header.php");
if (isset($user) && $user['is_admin']) {

  $year = get_setting('year');
  echo "</head>\n<body>\n";

  echo "<h2>Administer både</h2>\n";

  echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";


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

  $baade = array();
  $tilmeldte = array();
  $personer = array();
  $formaend = array();
  $minimum_timer = 1;

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

    // Find minimum timetal
    $res = $link->query("SELECT timer FROM dsr_vinter_roer_kategori
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

    $total_tilmeldt_antal = 0;
    $total_tilmeldt_timer = 0;
    $total_max_timer = 0;

    ?>

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
      }
      
      ?>

        <tr class="baad_raekke <?=$class?>">
           <td><?=$c_baad['navn']?></td>
           <td><?=$c_baad['periode']?></td>
           <td><?=$status?></td>
           <td><?=$antal?></td>
           <td><?=$timer?></td>
           <td><?=$c_baad['max_timer']?></td>
           <td><form class="table-button-form" action="admin_baade.php" method="POST">
                 <?=$form_fields?>
                 <input type="hidden" name="action" value="edit_boat" />
                 <input type="hidden" name="baadID" value="<?=$c_baad['ID']?>" />
                 <input type="submit" value="Rediger" />
               </form>
           </td>

           <td><form class="table-button-form" action="admin_baade.php" method="POST" onsubmit="return confirm('Er du sikker på, at du vil slette <?= $c_baad['navn'] ?>?')" >
                 <?=$form_fields?>
                 <input type="hidden" name="action" value="delete_boat" />
                 <input type="hidden" name="baadID" value="<?=$c_baad['ID']?>" />
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
include("footer.php");
?>
