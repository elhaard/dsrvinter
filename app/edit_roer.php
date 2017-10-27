<?php


include("inc/header.php");
if (isset($user) && $user['is_admin']) {

  $year = get_setting('year');
  echo "</head>\n<body>\n";

  $action = "";
  if (isset($_POST['action'])) {
    $action = $_POST['action'];
  }
  $edit = false;
  if ($action == "edit") {
    echo "<h2>Rediger roer</h2>\n";
    $edit = true;
  } else if ($action == "new") {
    echo "<h2>Opret roer</h2>\n";
  } else {
    echo "<h2>Fejl</h2>\n";
  }

  if ($action == "new" || $action == "edit") {
     $personID = 0;
     if (isset($_POST['personID'])) {
     	$personID = (int) $_POST['personID'];
     }

     $person = ["ID" => '',
                "navn" => '',
                "tlf" => '',
                "email" => '',
		"hours" => '',
                "km" => '',
		"kode" => '',
		"is_admin" => 0
               ];
     if ($edit) {
       $res = $link->query("SELECT * FROM person WHERE ID = " . $personID);
       if (! $res) {
          echo "<p class=\"error\">Fejl: Kunne ikke finde roer!!!</p>";
            $edit = false;
       } else {
          $row = $res->fetch_assoc();
          if ($row && isset($row["ID"]) && $row["ID"] == $personID) {
	    $person = $row;
          } else {
	    echo "<p class=\"error\">Fejl: Kunne ikke finde roer!!!</p>";
            $edit = false;
	  }
	  $res->close();
       }
     }

    ?>

    <form action="admin_roere.php" method="POST">
        <?=$form_fields?>
        <input type="hidden" name="action" value="<?= $edit ? 'edit_rower' : 'new_rower' ?>" />

        <label for="personID">Medlemsnummer:</label>
<?php
     if ($edit) {
       echo "<span id=\"personID\"><b> " . $person['ID'] . "</b></span><br/>\n";
       echo "<input type=\"hidden\" name=\"personID\" value=\"" . $personID . "\" />";
     } else {
?>
        <input type="text" id="personID" name="personID" size="5" value="<?= $person['ID'] ?>" /><br/>
<?php
     }
?>

        <label for="navn">Navn:</label>
        <input type="text" id="navn" name="navn" size="50" value="<?= $person['navn'] ?>" /><br/>

        <label for="email">Email:</label>
        <input type="text" id="email" name="email" size="50" value="<?= $person['email'] ?>" /><br/>

        <label for="tlf">Telefon:</label>
        <input type="text" id="tlf" name="tlf" size="20" value="<?= $person['tlf'] ?>" /><br/>


        <label for="km">Kilometer:</label>
        <input type="text" id="km" name="km" size="5" value="<?= $person['km'] ?>" /><br/>

        <label for="hours">Timer:</label>
        <input type="text" id="hours" name="hours" size="5" value="<?= $person['hours'] ?>" /><br/>


<?php
      if ($edit) {
?>
        <label for="kode">Kode:</label>
        <input type="text" name="kode" id="kode" size="20" value="<?= $person['kode'] ?>" /><br/>
  
<?php
      }
?>        

        <label for="rower_admin">Administrator:</label>
        <input type="checkbox" id="rower_admin" name="rower_admin" value="1" <?= $person['is_admin'] ? 'checked="checked"' : '' ?> /><br/>

        
        <input type="submit" value="<?= $edit ? 'Gem' : 'Opret' ?>"/>
    </form>
    <form action="admin_roere.php" method="post"><?= $form_fields ?><input type="submit" value="Annuller" /></form>

   <?php

  }
}

echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";
include("inc/footer.php");
?>


