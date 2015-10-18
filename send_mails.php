<?php


include("header.php");
if (isset($user) && $user['is_admin']) {
  echo "</head>\n<body>\n";

  require("mail_sender.php");
  echo "<h2>Sender mails</h2>\n";

     $res = $link->query("SELECT * FROM dsr_vinter_person WHERE email_sent IS NULL or email_sent = 0");
     if ($res) {
	$count = $res->num_rows;
        $total = 0;
	if ($count > 0) {
	   echo "<p id=\"sending_mails\">Sender $count mails....</p>";

           $template = get_setting("welcome_mail");
	   while ($row = $res->fetch_assoc()) {
               $mail_error = send_email("Tilmelding til vintervedligehold", $template, $row);
               if ($mail_error) {
                  echo "<p class=\"error\">Fejl: $mail_error</p>\n";
               } else {
		  $link->query("UPDATE dsr_vinter_person SET email_sent = 1 WHERE ID = " . (int) $row['ID']);
                  $total++;
               }
           }
           $res->close();
           ?>
              <script type="text/javascript">
                 document.getElementById('sending_mails').style.display = 'none';
              </script>
              <p class="ok">Færdig!</p>
              <p>Sendte mails til <?= $total ?> ud af <?= $count ?> personer</p>
           <?php
        } else {
	   echo "<p>Der er ingen, der mangler at få tilsendt mails.</p>\n";
       }
    } else {
	echo "<p class=\"error\">Kunne ikke finde manglende mails: <code>" . $link->error . "</code></p>\n";
    }

    echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\"/></form>\n";
   
}
include("footer.php");
?>
