<?php

header('Content-Type: text/html; charset=utf-8');

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">


<html>
  <head>
<title>DSR Vintervedligehold</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">

<link rel="stylesheet" type="text/css" href="vinter_style.css" />

</head>
<body>

<h1>Glemt password</h1>

<?php
   $sent = false;

   if (! (isset($_POST['send_password']) && $_POST['send_password'] == '1')) {
      echo "<p>Hvis du har glemt dit password - eller ikke har fået et - kan du få det tilsendt.</p>";
   } elseif (isset($_POST['medl_nr']) && trim($_POST['medl_nr']) != '' && ( (int) trim($_POST['medl_nr'])) > 0) {
	$medl_nr =  (int) trim($_POST['medl_nr']);
	require("sql.php");
        require("mail_sender.php");
	$res = $link->query("SELECT * FROM  dsr_vinter_person WHERE ID = " . (int) $medl_nr);
        if ($res) {
	    $person = $res->fetch_assoc();
            if ($person) {
               $template = get_setting("forgot_mail");
               $mail_error = send_email("Kode til vintervedligehold", $template, $person);
               if ($mail_error) {
                  echo "<p class=\"error\">Fejl: Kunne ikke afsende mail: $mail_error</p>\n";
               } else {
		 $sent = true;
               }
	    } else {
		echo "<p class=\"error\">Vi kunne ikke finde dig i systemet</p>";
                echo "<p>Enten har du tastet dit medlemsnummer forkert, eller også har du ikke roet nok i år til at du skal lave vintervedligehold.</p>";
                echo "<p>Kontakt materieludvalget, hvis du gerne vil lave vintervedligehold alligevel.</p>";
           }
           $res->close();
       } else {
	  echo "<p class=\"error\">Fejl: Kunne ikke kontakte databasen.</p>";
       }
   } else {
      echo "<p class=\"error\">Du glemte at indtaste dit medlemsnummer!</p>";
   }

   if ($sent) {
      echo "<p class=\"ok\">Vi har sendt dig en mail med en kode. Hvis du ikke har fået mailen, så kig i dit spamfilter.</p>";
   } else {
      ?>
     <form action="glemt.php" method="post">
        <b>Indtast dit medlemsnummer: </b>
        <input type="text" size="5" name="medl_nr" />
	<br />
        <input type="hidden" name="send_password" value="1" />
	<input type="submit" value="OK" />
     </form>
   <?php
   }
?>

<br/>
<form action="index.php" method="get">
  <input type="submit" value="Tilbage til login-siden" />
</form>

    
</body>
</html>
