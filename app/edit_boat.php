<?php


include("inc/header.php");
$year = get_setting('year');
echo "</head>\n<body>\n";
if (isset($user) && $user['is_admin']) {
  if (isset($_POST['action']) && ($_POST['action'] == 'edit_boat' || $_POST['action'] == 'new_boat')) {
	$edit = $_POST['action'] == 'edit_boat';

	if ($edit && isset($_POST['boatID'])) {
		$boatID = (int) $_POST['boatID'];
	} else {
		$edit = false;
	}

	if ($edit) {
	  echo "<h2>Rediger båd</h2>\n";
	} else {
	  echo "<h2>Opret ny båd</h2>\n";
	}

    echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";

    $boat = [];
    $team = [];
    $teams = [];
    $categories = [];
    if ($edit) {
	    $res = $link->query("SELECT * FROM baad WHERE ID = " . $boatID);
      if ($res) {
        $boat = $res->fetch_assoc();
        $res->close();
	    } else {
        echo "<p class=\"error\">Kunne ikke finde båden: " . $link->error . "</p>";
      }
    }
    $res = $link->query("SELECT t.*,
                         GROUP_CONCAT(b.navn ORDER BY b.navn SEPARATOR '/') as boat_names
                         FROM team t
                         LEFT JOIN baad b ON b.team = t.ID
                         GROUP BY t.ID
                         ORDER BY boat_names ASC");
    if ($res) {
    	while ($row = $res->fetch_assoc()) {
    		array_push($teams, $row);
        if (isset($boat['team']) && $boat['team'] == $row['ID']) {
          $team = $row;
        }
    	}
      $res->close();
    } else {
      echo "<p class=\"error\">Kunne ikke finde bådhold: " . $link->error . "</p>";
    }


    $res = $link->query("SELECT * FROM baadtype ORDER BY type ASC");
    if ($res) {
    	while ($row = $res->fetch_assoc()) {
    		array_push($categories, $row);
    	}
    } else {
      echo "<p class=\"error\">Kunne ikke finde bådtyper: " . $link->error . "</p>";
    }

?>
    <form action="admin_baade.php" method="POST">
        <?=$form_fields?>
        <input type="hidden" name="action" value="<?= $edit ? 'edit_boat' : 'new_boat' ?>" />
<?php
      if ($edit) {
      	echo "<input type=\"hidden\" name=\"boatID\" value=\"" . $boatID . "\" />";
      }
?>
        <label for="name">Navn:</label>
        <input type="text" id="name" name="name" size="50" value="<?= $edit ? $boat['navn'] : '' ?>" /><br/>

        <label for="type">Type:</label>
        <select name="type" id="type">
<?php
	  foreach ($categories as $category) {
	  	echo "<option value=\"" . $category['ID'] . "\"";
	  	if ($category['ID'] == $boat['type']) {
	  		echo " selected=\"selected\"";
	  	}
	  	echo ">" . htmlspecialchars($category['type']) . "</option>";
	  }
?>
        </select>
        <input type="text" name="new_type" placeholder="For ny type, skriv her" size="40" /><br/>

        <label for="period">Periode:</label>
        <select name="period" id="period">
          <option value="efterår" <?= $edit && $team['period'] == 'efterår' ? 'selected="selected"' : '' ?>>Efterår</option>
          <option value="forår" <?= $edit && $team['period'] == 'forår' ? 'selected="selected"' : '' ?>>Forår</option>
          <option value="hele vinteren" <?= $edit && $team['period'] == 'hele vinteren' ? 'selected="selected"' : '' ?>>Hele vinteren</option>
        </select>

        <label for="hours">Timer:</label>
        <input type="text" name="hours" id="hours" size="4" value="<?= $edit ? $boat['max_timer'] : '' ?>" /><br/>

        <label for="description">Beskrivelse:</label><br/>
        <textarea rows="10" cols="74" name="description" id="description"><?= $edit ? htmlspecialchars($boat['beskrivelse']) : ''?></textarea>
        <br/>

        <label for="type">Bådhold:</label>
        <select name="team" id="team">
       <?php
         if (!$edit) {
           echo "<option value=\"new\"> -- Separat bådhold -- </option>\n";
         }
         foreach ($teams as $c_team) {
           echo "<option value=\"" . $c_team['ID'] . "\"";
           if (isset($boat['team']) && $c_team['ID'] == $boat['team']) {
             echo " selected=\"selected\"";
           }
           echo ">" . htmlspecialchars($c_team['boat_names']) . "</option>";
         }
        ?>
        </select>

      	<label for="hidden">Skjult bådhold</label>
	      <input name="hidden" id="hidden" type="checkbox" value="1" <?= $team['hidden'] ? 'checked="checked"' : ''?> /><br/><br/>
        <input type="submit" value="<?= $edit ? 'Gem' : 'Opret' ?>"/>
    </form>

   <?php

  }
}

echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";
include("inc/footer.php");
?>
