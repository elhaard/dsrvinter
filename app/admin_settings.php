<?php


include("inc/header.php");
if (isset($user) && $user['is_admin']) {

  $year = get_setting('year');
  echo "</head>\n<body>\n";

  echo "<h2>Indstillinger</h2>\n";

  echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";

  if (isset($_POST['action']) && $_POST['action'] && isset($_POST['settingID']) && (int) $_POST['settingID']) {
     $id = (int) $_POST['settingID'];
     $action = trim($_POST['action']);

     if ($action == 'change_setting') {
	    $newValue = isset($_POST['newValue']) ? trim($_POST['newValue']) : '';
	    if ($newValue) {
           $stmt = $link->prepare("UPDATE settings SET content = ? WHERE ID = ? LIMIT 1");
           if ($stmt) {
           	  $stmt->bind_param('si', $newValue, $id);
           	  $stmt->execute();
           } else {
              echo "<p class=\"error\">Kunne ikke opdatere indstilling</p>\n";
              error_log("Setting update error: " . $link->error);
           }
        } else {
           echo "<p class=\"error\">Kan ikke opdatere til en tom værdi</p>\n";
        }
     
     } else {
         echo "<p class=\"error\">Ukendt action: '$action'</p>\n";
     }

  }


  echo "<h3>Indstillinger</h3>\n";

  
  // Find indstillinger
  $res = $link->query("SELECT * FROM settings ORDER BY name, id");
  if (! $res) {
     echo "<p class=\"error\">Fejl: Kunne ikke finde indstillinger!!!</p>";
     error_log("Could not get settings: " . $link->error);
  } else {
    while ($row = $res->fetch_assoc()) {
  
?>
      <form class="table-button-form" action="admin_settings.php" method="POST">
         <?=$form_fields?>
         <input type="hidden" name="action" value="change_setting" />
         <input type="hidden" name="settingID" value="<?=$row['id']?>" />
         <h4><?= $row['description'] ?></h4>
<?php 
       if ($row['type'] == 'number') {
       	 echo "<input type=\"text\" name=\"newValue\" size=\"10\" value=\"" . (int) $row['content'] . "\" />\n";
       } elseif ($row['type'] == 'string') {	 
       	 echo "<input type=\"text\" name=\"newValue\" size=\"72\" value=\"" . htmlspecialchars($row['content']) . "\" />\n";
       } else {
       	 echo "<textarea name=\"newValue\" cols=\"72\" rows=\"15\">". htmlspecialchars($row['content']) . "</textarea><br/>\n";
       }
?>
       <input type="submit" value="Gem indstilling" />
       </form>
       <hr/>
<?php 

    }
  }
?>
  <form class="table-button-form" action="delete_all.php" method="POST">
  <?=$form_fields?>
  <h3>Nulstil alt</h3>
  <p>Hvis du vil slette alle både og roere, kan du trykke på knappen herunder. Dette kan f.x. bruges ved opstart af et nyt år</p>
  <input type="hidden" name="action" value="start_delete_all" />
  <input type="submit" class="boom" onClick="return(confirm('Vil du virkelig slette ALLE både og roere???'))" value="Slet alle både og roere" />
  <hr/>  
<?php 
  
}  
echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";
include("inc/footer.php");
$link->close();
?>
