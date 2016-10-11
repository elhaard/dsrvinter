<?php


include("inc/header.php");
if (isset($user) && $user['is_admin']) {
  echo "</head>\n<body>\n";

  require("inc/mail_sender.php");
  echo "<h2>Sender mails</h2>\n";

  $recipients = array();

     $res = $link->query("SELECT ID, email, navn FROM person WHERE email_sent IS NULL or email_sent = 0");
     if ($res) {
	$count = $res->num_rows;
        $total = 0;
	if ($count > 0) {
	   while ($row = $res->fetch_assoc()) {
               $recipients[] = $row;
           }
           ?>
           <div id="error-div" class="error" style="display: none">Følgende ting gik galt:<ul id="error-list"></ul></div>
           <div id="sender-status">Sender <?= $count ?> mails....</div>
           <div id="sender-progress"></div>
              <script type="text/javascript">
                 var recipients = <?= json_encode($recipients) ?>;
                 var error_div = document.getElementById('error-div');
                 var error_list = document.getElementById('error-list');
                 var sender_status = document.getElementById('sender-status');
                 var sender_progress = document.getElementById('sender-progress');

		 function encode_data(data) {
		     var query = [];
                     for (var key in data) {
                        query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
                     }
                     return query.join("&");
                 }

                 function addError (msg, user) {
		    error_div.style.display  = 'block';
		    var li = document.createElement('li');
                    li.innerHTML = 'Kunne ikke sende mail til ' + user.navn + ' (' + user.ID + '): ' + msg;
                    error_list.appendChild(li);
                 }

                 var len = recipients.length;
                 var ok_count = 0;
		 for (var i = 0; i < len; i++) {
                     sender_progress.innerHTML = (1 + i) + ': Sender til ' + recipients[i].navn + '(' + recipients[i].ID + '): ' + recipients[i].email;
                     xhttp = new XMLHttpRequest();
		     xhttp.open("POST", "ajax_send_mail.php", false); // Synchronous!
		     xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		     data = { 'medlemsnummer' : '<?= $user['ID'] ?>',
                              'password':  '<?= $user['kode'] ?>',
                              'recipient': recipients[i]['ID'],
                              'subject': 'Tilmelding til vintervedligehold',
                              'template': 'welcome_mail',
                              'mark_sent': true
                            };
                     xhttp.send(encode_data(data));
		     if (xhttp.readyState == 4 && xhttp.status == 200) {
		        var res = JSON.parse(xhttp.responseText);
			if ( res.success ) {
                           ok_count++;
                        } else if (typeof res.error === 'string') {
                           addError(res.error, recipients[i]);
                        } else {
                           addError('Forstod ikke svaret fra mailserveren', recipients[i]);
                        }
                     } else {
                        addError("Ajax-fejl", recipients[i]);
                     }
                 }
		 sender_progress.style.display = 'none';
                 sender_status.innerHTML = 'Færdig: Sendte ' + ok_count + ' ud af <?= $count ?> mails.';
                 sender_status.className = 'ok';
              </script>
           <?php
        } else {
	   echo "<p>Der er ingen, der mangler at få tilsendt mails.</p>\n";
       }
        $res->close();
    } else {
	echo "<p class=\"error\">Kunne ikke finde manglende mails: <code>" . $link->error . "</code></p>\n";
    }

    echo "<form action=\"baadvalg.php\" method=\"post\">$form_fields<input type=\"submit\" value=\"Tilbage til oversigten\"/></form>\n";
   
}
include("inc/footer.php");
?>
