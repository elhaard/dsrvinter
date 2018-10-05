<?php


include("inc/header.php");
if (isset($user) && $user['is_admin']) {

  $year = get_setting('year');
  echo "</head>\n<body>\n";

  echo "<h2>Slet alt</h2>\n";

  echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";

  if (isset($_POST['action']) && $_POST['action']) {
     $action = trim($_POST['action']);

     if ($action == 'start_delete_all') {
     	$random = rand(1,999);
?>
     <h3>Vil du virkelig slette alt?</h3>
     <form class="table-button-form" action="delete_all.php" method="POST">
       <?=$form_fields?>
       <input type="hidden" name="action" value="really_delete_all" />
       <input type="hidden" name="random" value="<?=$random?>" />

       For at være helt sikker på, at du mener det, skal du indtaste tallet <b><?=$random?></b> i feltet herunder:
       <br /><br />
       <input type="text" name="check" size="4"/>
       <br />
       <input type="submit" value="Ja, slet virkelig alt!" />
     </form>
     <hr/>
<?php
     } elseif ( $action="really_delete_all") {
       if (   isset($_POST['check'])
     	   && isset($_POST['random'])
     	   && $_POST['check']
     	   && $_POST['check'] == $_POST['random'] ) {

          if ($link->begin_transaction()) {
            $res =  $link->query("DELETE FROM baadformand")
                 && $link->query("DELETE FROM person WHERE id <> " . (int) $user['ID'])
                 && $link->query("DELETE FROM baad")
                 && $link->query("DELETE FROM team")
                 && $link->query("DELETE FROM baadtype");
            if ($res && $link->commit()) {
            	echo "<p class=\"ok\">Alt blev slettet</p>\n";
            	echo "<p>Hvis du starter et nyt år, så husk at ændre årstallet under indstillinger.</p>";
            } else {
            	echo "<p class=\"error\">Fejl: Kunne ikke slette!</p>\n";
            	error_log("Could not delete all: " . $link->error);
            	$link->rollback();
            }
          } else {
          	echo "<p class=\"error\">Fejl: Kunne ikke gøre klar til sletning</p>\n";
          	error_log("Could not begin transaction for delete_all: " . $link->error);
          }
       } else {
       	echo "<p class=\"error\">Du har ikke indtastet det rigtige tal</p><p>Gå tilbage og prøv igen</p>\n";
       }
     } else {
         echo "<p class=\"error\">Ukendt action: '$action'</p>\n";
     }
  }
}
echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\" /></form>\n";
include("inc/footer.php");
?>
