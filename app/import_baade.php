<?php


include("inc/header.php");
include("inc/csvParse.php");

if (isset($user) && $user['is_admin']) {

  ?>
  </head>
  <body>

  <h2>Importér både</h2>

  <?php
    $show_input = true;
    if (    isset($_POST['import_boats'])
         && (  (    $_POST['import_boats'] == '1'
    	         && isset($_POST['new_boats'])
         	   )
            || (    $_POST['import_boats'] == '2'
    	         && isset($_FILES['boat_file'])
         	   )
            )
      ) {
        $show_input = false;

        // Find eksisterende både
        $baade = array();
        $res = $link->query("SELECT * from baad");
        $error = array();
        $ok = array();
	    if ($res) {
            while ($row = $res->fetch_assoc()) {
                $baade[ strtolower($row['navn']) ] = $row;
            }
            $res->close();
        }

        // Find typer
        $typer = array();
        $res = $link->query("SELECT * from baadtype");
	    if ($res) {
            while ($row = $res->fetch_assoc()) {
                $typer[ strtolower($row['type']) ] = $row['ID'];
            }
            $res->close();
        }
		$input = '';
        if ( $_POST['import_boats'] == '1' ) {
        	$input = $_POST['new_boats'];
        } else {
        	$file = $_FILES["boat_file"]["tmp_name"];
        	$fh = fopen($file, "r");
        	$input = fread($fh, filesize($file));
        	fclose($fh);
        }

        $lines = csvParse($input);
        $lineno = 0;
        foreach ($lines as $fields) {
          $lineno++;
          if (count($fields) == 4) {
            $fields[] = '';
          } elseif (count($fields) != 5) {
            $error[] = "Linie $lineno: Forkert antal felter. Springer over...";
             continue;
          }
          $navn = trim($fields[0]);
          $type = trim($fields[1]);
          $timer = (int) trim($fields[2]);
          $saeson = trim($fields[3]);
          $beskrivelse = trim($fields[4]);
          if ($saeson == 'e' || $saeson == 'E' || preg_match("/efterår/i", $saeson) ) {
             $saeson = "efterår";
          } elseif ($saeson == 'f' || $saeson == 'F'|| preg_match("/forår/i", $saeson)) {
             $saeson = "forår";
          } elseif ($saeson == 'v' || $saeson == 'V'|| preg_match("/vinter/i", $saeson)) {
             $saeson = "hele vinteren";
          } else {
             $error[] = "Linie $lineno: Ukendt sæson '$saeson'. Indsætter alligevel";
          }
          if ($timer <= 0) {
             $error[] = "Linie $lineno: Ingen timer. Indsætter alligevel";
             $timer = 0;
          }
          if (isset($typer[strtolower($type)])) {
          	$type_id = $typer[strtolower($type)];
          } else {
             // Opret baadtype
             $res = $link->query("INSERT INTO baadtype (type) VALUES ('" . $link->escape_string($type) . "')");
             if ($res) {
               $type_id = $link->insert_id;
               $typer[ strtolower($type) ] = $type_id;
               $ok[] = "Oprettede ny bådtype <b>$type</b>";
             } else {
                $error[] = "Linie $lineno: Kunne ikke oprette bådtypen <i>$type</i>. Opretter ikke båden <i>$navn</i>";
                continue;
             }
          }
          if ($baade[strtolower($navn)]) {
             $id = $baade[strtolower($navn)]['ID'];

             $res = $link->query("UPDATE baad SET type = " . (int) $type_id .
                                 ", navn = '" . $link->escape_string($navn) .
                                 "', max_timer = " . (int) $timer  .
                                 ", beskrivelse = '" . $link->escape_string($beskrivelse) .
                                 "' WHERE ID = " . (int) $id);
             if ($res) {
                $ok[] = "Linie $lineno: <i>$navn</i> eksisterer allerede. Opdaterer";
             } else {
                $error[] = "Linie $lineno: Kunne ikke opdatere eksisterende båd <i>$navn</i>: " . $link->error;
             }
             $res = $link->query("UPDATE team SET PERIOD = '" .
                                  $link->escape_string($saeson) .
                                  "' WHERE ID = " .
                                  (int) $baade[strtolower($navn)]['team']);
             if (!$res) {
               $error[] = "Linie $lineno: Kunne ikke sætte opdatere periode for bådhold for <i>$navn</i>: " . $link->error;
             }
          } else {
            $res = $link->query("INSERT INTO team (period) VALUES ('" .
                                 $link->escape_string($saeson) .
                                 "')" );
            if ($res) {
              $teamID = $link->insert_id;

              $res = $link->query("INSERT INTO baad (type, navn, max_timer, team, beskrivelse) VALUES (" .
                                 (int) $type_id . ", '" .
                                 $link->escape_string($navn) . "', " .
                                 (int) $timer . ", " .
                                 (int) $teamID . ", '" .
                                 $link->escape_string($beskrivelse) . "')"
                               );
              if ($res) {
                 $id = $link->insert_id;
                 $ok[] = "Oprettede båden <i>$navn</i> med ID $id, team $teamID";
                 $baade[strtolower($navn)] = array( 'ID' => $id,
                                                    'navn' => $navn,
                                                    'max_timer' => $timer,
                                                    'type' => $type_id,
                                                    'team' => $teamID,
                                                    'beskrivelse' => $beskrivelse );
              } else {
                 $error[] = "Linie $lineno: Kunne ikke oprette båden <i>$navn</i>: " . $link->error;
              }
            } else {
              $error[] = "Linie $lineno: Kunne ikke oprette bådhold til båden <i>$navn</i>: " . $link->error;
            }
          }
        }

        if (count($error) > 0) {
          echo "<p class=\"error\">Der var fejl under importen: <ul>";
          foreach ($error as $err) {
            echo "<li>$err</li>\n";
          }
          echo "</ul></p>\n";
        }
        if (count($ok) > 0) {
          echo "<p class=\"ok\">Følgende gik godt: <ul>";
          foreach ($ok as $msg) {
             echo "<li>$msg</li>\n";
          }
          echo "</ul></p>\n";
        }
    }

    if ($show_input) {


  ?>
  <p>Her kan du indsætte en liste af både, som skal oprettes. Formatet er:<br/>
  &nbsp;&nbsp;<code>navn;bådtype;timer;sæson;beskrivelse</code><br/>
  <br/>
  Eksempel:<br/>
  &nbsp;&nbsp;<code>Nanna;4-åres inrigger;300;f;Skal blot pletlakeres</code></p>

  <p>Du skal <b>ikke</b> have kolonneoverskrifter!</p>
  <p>Det er vigtigt, at den samme bådtype er stavet ens hver gang - ellers oprettes to forskellige bådtyper. Sæson kan være <b><code>f</code></b> (forår),
    <b><code>e</code></b> (efterår) eller <b><code>v</code></b> (hele vinteren).</p>

    <form action="import_baade.php" method="post">
       <input type="hidden" name="import_boats" value="1" />
       <?= $form_fields ?>

       <textarea name="new_boats" cols="100" rows="20" placeholder="Indsæt oplysninger her"></textarea>
       <br/>
       <input type="submit" value="Opret både" />
    </form>
    <br />
    <form action="import_baade.php" method="post" enctype="multipart/form-data">
         <input type="hidden" name="import_boats" value="2" />
         <?= $form_fields ?>
         Du kan også uploade en CSV-fil (uden overskrifter) i stedet for:
		 <input type="file" name="boat_file" id="boat_file">
         <br/>
         <input type="submit" value="Upload fil med både" />
    </form>

    </p>

  <?php

    }

    echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\"/></form>\n";

}
include("inc/footer.php");
?>
