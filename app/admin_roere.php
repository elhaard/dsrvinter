<?php

include("inc/header.php");
if (isset($user) && $user['is_admin']) {

  $year = get_setting('year');
  $booking_factor = ((int) get_setting('booking_percentage')) / 100;
  echo "</head>\n<body>\n";

  echo "<h2>Administer roere</h2>\n";

  echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";
  echo "<form action=\"import_roere.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Importer roere\" /></form>\n";
  echo "<form action=\"edit_roer.php\" method=\"post\">$form_fields<input type=\"hidden\" name=\"action\" value=\"new\" /><input type=\"submit\" value=\"Ny roer\" /></form>\n";


  if (isset($_POST['action']) && $_POST['action'] && isset($_POST['personID'])) {
     $id = (int) $_POST['personID'];
     $action = trim($_POST['action']);

     if ($action == 'change_rower_boat' && $id) {
	      $teamID = isset($_POST['teamID']) ? (int) trim($_POST['teamID']) : 0;
        $be_formand = ( isset($_POST['be_formand']) && trim($_POST['be_formand']) );
	      $newBoat = ( $teamID > 0 ) ? (int) $teamID : 'NULL';
           $res = $link->query("UPDATE person SET team = $newBoat WHERE ID = $id LIMIT 1");
           if ($res) {
              $res = $link->query("DELETE FROM baadformand WHERE formand = $id LIMIT 1");
              if ($res) {
                  if ($be_formand) {
	             $res = $link->query("INSERT INTO baadformand (team, formand) VALUES ($newBoat, $id)");
                     if (!$res) {
                        echo "<p class=\"error\">Kunne ikke sætte ny bådformand</p>\n";
                     }
                  }
              } else {
                    echo "<p class=\"error\">Kunne ikke slette gamle formandsoplysninger</p>\n";
              }
          } else {
              echo "<p class=\"error\">Kunne ikke sætte ny båd</p>\n";
          }
     } elseif ($action == 'delete_rower' && $id) {
         if ( $link->query("DELETE FROM baadformand WHERE formand = $id LIMIT 1") ) {
            if ( $link->query("DELETE FROM person WHERE ID = $id LIMIT 1") ) {
		echo '<p class="ok">Slettede personen</p>';
            } else {
               echo '<p class="error">Kunne ikke slette personen</p>';
            }
         } else {
	       echo '<p class="error">Kunne ikke slette formandsoplysninger - personen er ikke slettet</p>';
         }


     } else if ($action == 'edit_rower' && $id) {
       $sth = $link->prepare("UPDATE person SET navn = ?, email = ?, tlf = ?, kode = ?, hours = ?, km = ?, is_admin = ? WHERE ID = ?");
       $res = false;
       if ($sth) {
         $rower_admin = (isset($_POST['rower_admin']) && $_POST['rower_admin'] == 1) ? 1 : 0;
         $sth->bind_param("ssssiiii",
			  $_POST['navn'],
                	  $_POST['email'],
		  	  $_POST['tlf'],
                    	  $_POST['kode'],
                    	  $_POST['hours'],
                    	  $_POST['km'],
          		  $rower_admin,
  		          $id
	       		 );
         $res = $sth->execute();
       }
       if (!$res) {
         echo "<p class=\"error\">Kunne ikke gemme roer</p>";
         error_log("Could not update rower: " . $link->error);
       }
     } else if ($action == 'new_rower') {
       if ($id > 0) {
         $res = false;
       	 $sth = $link->prepare("INSERT INTO person (ID, navn, email, tlf, kode, hours, km, is_admin) VALUES (?,?,?,?,?,?,?,?)");
         if ($sth) {
           $rower_admin = (isset($_POST['rower_admin']) && $_POST['rower_admin'] == 1) ? 1 : 0;
	   $pw = generate_password();
           $sth->bind_param("issssiii",
                            $id,
	  		    $_POST['navn'],
			    $_POST['email'],
			    $_POST['tlf'],
			    $pw,
			    $_POST['hours'],
			    $_POST['km'],
          		    $rower_admin
			   );
           $res = $sth->execute();
	 }
	 if (!$res) {
	   echo "<p class=\"error\">Kunne ikke oprette roer</p>";
           error_log("Could not insert rower: " . $link->error);
         }
       } else {
         echo "<p class=\"error\">Ugyldigt medlemsnummer!</p>";
       }
     } else {
         echo "<p class=\"error\">Ukendt action: '$action'</p>\n";
     }

  }


  $teams = array();
  $teamsById = array();
  $personer = array();
  $formaend = array();


  // Find teams
  $res = $link->query("SELECT t.* FROM team t,
                       GROUP_CONCAT(b.navn ORDER BY b.navn SEPARATOR '/') as boat_names
                       SUM(b.max_timer) as max_hours
                       LEFT JOIN baad b ON t.ID = b.team
                       GROUP BY t.*
                       ORDER BY boat_names, t.ID");

  if (! $res) {
     echo "<p class=\"error\">Fejl: Kunne ikke finde bådholdsliste!!!</p>";
  } else {
    while ($brow = $res->fetch_assoc()) {
        $id = $brow['ID'];
        $brow['antal'] = 0;
        $brow['timer'] = 0;
        $teamsById[$id] = $brow;
        $teams[] = $teamsById[$id];
    }
    $res->close();
  }


  // Find roere
  $res = $link->query("SELECT p.*
                       FROM person p
                       ORDER BY p.navn, p.ID");
  if ($res) {
      while ($prow = $res->fetch_assoc()) {
         $personer[] = $prow;
         if ( $prow['team'] ) {
            $baadeById[ $prow['team'] ]['antal']++;
            $baadeById[ $prow['team'] ]['timer'] += $prow['hours'];
         }
      }
      $res->close();

  } else {
     echo "<p class=\"error\">Fejl: Kunne ikke finde roere!!!</p>";
  }

  $res = $link->query("SELECT * FROM baadformand");
  if ($res) {
     while ($row = $res->fetch_assoc()) {
        $fm = $row['formand'];
        if (! isset( $formaend[ $fm ] )) {
            $formaend[$fm] = array();
        }
        $formaend[$fm][ $row['team'] ] = $row['id'];;
     }
     $res->close();
  }

  ?>

    <h3>Roere</h3>
    <table class="roer_tabel">
      <tr>
        <th>Medlemsnummer</th>
        <th>Navn</th>
        <th>Timer</th>
        <th>Bådhold</th>
        <td>&nbsp;</td>
     </tr>
    <?php
  $total_timer = 0;
  $total_antal = 0;
  $total_tilmeldt_antal = 0;
  $total_tilmeldt_timer = 0;
  foreach( $personer as $person ) {
    $id = $person['ID'];

    $total_antal++;
    $total_timer += $person['hours'];

    $class = '';
    if ( $person['team'] ) {
       $class = 'tilmeldt';
       $total_tilmeldt_antal++;
       $total_tilmeldt_timer += $person['hours'];
    } else {
       $class = 'ledig';
    }
    $mark = ( isset($_POST['mark_person']) && trim($_POST['mark_person']) == $id );
    $mark_start  = $mark ? '<a name="mark">' : '';
    $mark_end    = $mark ? '</a>' : '';
      ?>

        <tr class="person_raekke <?=$class?>">
           <td><?=$mark_start?><?=$id?><?=$mark_end?></td>
           <td title="Eget ønske: <?= $person['wished_team'] ? $baadeById[ $person['wished_team']]['boat_names'] : 'intet' ?>"><?=$person['navn']?></td>
           <td><?=$person['hours']?></td>
           <td><form class="table-button-form" action="admin_roere.php#mark" method="POST">
                 <?=$form_fields?>
                 <input type="hidden" name="action" value="change_rower_boat" />
                 <input type="hidden" name="personID" value="<?=$id?>" />
                 <input type="hidden" name="mark_person" value="<?=$id?>" />
                 <select name="teamID">
                    <option value="0" <?= $person['team'] ? '' : 'selected="selected"' ?>> <i>-- ingen --</i></option>
           <?php
              foreach ($teams as $c_team) {
                 if ($c_team['ID'] == $person['team']) {
                     echo '<option value="' . $c_team['ID'] . '" selected="selected" class="selected-boat">' . $c_team['boat_names'] . "</option>\n";
                 } else {
                     if ($c_team['timer']  > $booking_factor * $c_team['max_hours'] + 3 ) {
                        $class="optaget overfuld";
                     } elseif ($c_team['timer']  >= $booking_factor * $c_team['max_hours']) {
                        $class="optaget";
                     } elseif ($c_team['timer'] + $person['hours'] > $booking_factor * $c_team['max_hours']) {
                        $class="optaget taet-paa";
                     } else {
                        $class="ledig";
                     }
                     echo '<option value="' . $c_team['ID'] . "\" class=\"$class\">" . $c_team['boat_names'] . "</option>\n";
                 }
              }
           ?>
                 </select>
                 Formand: <input type="checkbox" name="be_formand" value="1" <?= isset($formaend[ $id ]) ? 'checked="checked"' : '' ?> />
                 <input type="submit" value="Skift bådhold" />
               </form>
           </td>

           <td><form class="table-button-form" action="edit_roer.php" method="POST">
                 <?=$form_fields?>
                 <input type="hidden" name="action" value="edit" />
                 <input type="hidden" name="personID" value="<?=$id?>" />
                 <input type="submit" value="Rediger" />
               </form>
               <form class="table-button-form" action="admin_roere.php" method="POST" onsubmit="return confirm('Er du sikker på, at du vil slette <?= $person['navn'] ?>?')" >
                 <?=$form_fields?>
                 <input type="hidden" name="action" value="delete_rower" />
                 <input type="hidden" name="personID" value="<?=$id?>" />
                 <input type="submit" value="Slet"/>
               </form>
           </td>
        </tr>
     <?php

  }

  ?>
     </tr>
   </table>

   <?php
    printf("Ialt %d roere, %d timer", $total_antal, $total_timer);
  if ($total_antal) {
     printf(", gennemsnit %01.2f timer/roer.<br/>\n", $total_timer/$total_antal);
     printf("%d tilmeldte roere (%01.2f%%), %d timer (%01.2f%%).<br/>\n", $total_tilmeldt_antal,
							   	          100 * $total_tilmeldt_antal/$total_antal,
								          $total_tilmeldt_timer,
								          100 * $total_tilmeldt_timer/$total_timer);
	  } else {
     echo ".\n";
  }


}

echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";
include("inc/footer.php");
?>
