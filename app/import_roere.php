<?php


include("inc/header.php");
if (isset($user) && $user['is_admin']) {

  ?>
  </head>
  <body>

  <h2>Importér roere</h2>

  <?php
    $show_input = true;
    if (isset($_POST['import_rowers']) && $_POST['import_rowers'] == '1' && isset($_POST['new_rowers'])) {
        $show_input = false;

	// Find eksisterende roere
        $roere = array();
	$res = $link->query("SELECT * from person");
        $error = array();
        $ok = array();
	if ($res) {
            while ($row = $res->fetch_assoc()) {
                $roere[ 'm' . (int) $row['ID'] ] = $row;
            }
            $res->close();
        }

        // Find  både
        $baade = array();
	$res = $link->query("SELECT * FROM baad");
	if ($res) {
            while ($row = $res->fetch_assoc()) {
                $baade[ strtolower($row['navn']) ] = $row;
            }
            $res->close();
        }


        $lines = preg_split("/(\r|\n)+/", $_POST['new_rowers']);
	$lineno = 0;
        foreach ($lines as $line) {
	  $lineno++;
          $line = trim($line);
          if ($line == "") {
            continue;
          }
          $fields = explode(";", $line);

	  if (count($fields) != 6) {
	     $error[] = "Linie $lineno: Forkert antal felter. Springer over...";
             continue;
          }
	  $medlem_id = (int) trim($fields[0]);
	  $navn = trim($fields[1]);
          $km = (int) trim($fields[2]);
          $timer = (int) trim($fields[3]);
          $formand_for = strtolower(trim($fields[4]));
	  $email = strtolower(trim($fields[5]));
	  if ($medlem_id <= 0) {
	     $error[] = "Linie $lineno: Intet medlemsnummer. Springer over...";
             continue;
          }
	  if (! preg_match("/^[^@]+@[^@]+$/", $email)) {
	     $error[] = "Linie $lineno: Ugyldig emailadresse '$email'. Indsætter uden email-adresse.";
	     $email = '';
          }
	  $formandsbaad = 0;
	  if ($formand_for != '') {
	     if (isset($baade[$formand_for])) {
		$formandsbaad = $baade[$formand_for]['ID'];
             } else {
                $error[] = "Linie $lineno: Ukendt båd '$formand_for' - ignorer bådformandsrolle";
             }
          }

	  if (isset($roere['m' . $medlem_id])) {
             $baad_value = $formandsbaad ? $formandsbaad : $roere['m' . $medlem_id]['baad'];
	     if (! $baad_value) {
	        $baad_value = 'NULL';
             }

	     $res = $link->query("UPDATE person SET  " .
                                 " navn = '" . $link->escape_string($navn) . 
                                 "', km = " . (int) $km  .
                                 "', hours = " . (int) $timer  .
                                 ", email = '" . $link->escape_string($email) .
                                 "', baad = $baad_value" .
                                 " WHERE ID = " . (int) $medlem_id);
             if ($res) {
                $ok[] = "Linie $lineno: <i>$medlem_id ($navn)</i> eksisterer allerede. Opdaterer";
             } else {
                $error[] = "Linie $lineno: Kunne ikke opdatere eksisterende roer <i>$medlem_id ($navn)</i>: " . $link->error;
             }
	     $res = $link->query("DELETE FROM baadformand WHERE formand = " . (int) $medlem_id);
	     if (! $res ) {
                $error[] = "Linie $lineno: Kunne ikke fjerne formandsoplysninger: " . $link->error;
             }

          } else {
	     $baad_value = $formandsbaad ? (int) $formandsbaad : 'NULL';
	     $pw = generate_password();

	     $res = $link->query("INSERT INTO person (ID, navn, km, hours, email, baad, kode) VALUES (" .
                                 (int) $medlem_id . ", '" .
                                 $link->escape_string($navn) . "', " . 
                                 (int) $km . ", " .
                                 (int) $timer . ", '" .
                                 $link->escape_string($email) . "', " .
                                 $baad_value . ", '" .
                                 $link->escape_string($pw) . "')"
                               );
             if ($res) {
                $ok[] = "Oprettede roeren <i>$navn</i> med ID $medlem_id.";
		$roere['m' . $medlem_id] = array( 'ID' => $medlem_id,
                                                  'navn' => $navn,
                                                  'hours' => $timer,
                                                  'email' => $email
                                                );

             } else {
                $error[] = "Linie $lineno: Kunne ikke oprette roeren <i>$medlem_id ($navn)</i>: " . $link->error;
                continue;
             }
          }
          if ($formandsbaad) {
            $res = $link->query("INSERT INTO baadformand (formand, baad) VALUES ($medlem_id, $formandsbaad)");
	    if (!$res) {
               $error[] = "Linie $lineno: Kunne ikke sætte <i>$medlem_id ($navn)</i> som bådformand: " . $link->error;
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
	  
         ?>
         <p>Husk at sende invitationer til roerne, så de kan få deres password. Du kan gøre det nu eller senere.</p>
         <form action="send_mails.php" method="post" onsubmit="return confirm('Vil du sende invitationer til alle, der ikke allerede har fået invitation?')">
             <?= $form_fields ?>
             <input type="submit" value="Send invitationer nu" />
         </form>
        <?php

        }
    }

    if ($show_input) {


  ?>
  <p>Her kan du indsætte en liste af roere, som skal oprettes. Formatet er:<br/>
  &nbsp;&nbsp;<code>medlemsnummer;navn;kilometer;timer;bådformand for;email</code><br/>
  <br/>
  Eksempel:<br/>
  &nbsp;&nbsp;<code>8686;Jørgen Elgaard Larsen;1024;22;;jel@elgaard.net</code><br/>
  &nbsp;&nbsp;<code>6096;Anne Yde;205;12;Hjalte;aydexx@gmil.com</code></p>

  <p>Du skal <b>ikke</b> have kolonneoverskrifter eller gåseøjne!</p>
 
    <form action="import_roere.php" method="post"> 
       <input type="hidden" name="import_rowers" value="1" />
       <?= $form_fields ?>
       <textarea name="new_rowers" cols="100" rows="20" placeholder="Indsæt oplysninger her"></textarea>
       <br/>
       <input type="submit" value="Opret roere" />
    </form>

  <?php

    }

    echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\"/></form>\n";
   
}
include("inc/footer.php");
?>
