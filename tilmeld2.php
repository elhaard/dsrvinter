<?php

include("header.php");
if (isset($user)) {

?>
<script type="text/javascript">
   function check_felter() {
	var res = true;
	var msg = '';
	var tlf = document.getElementById('telefonfelt');
	var email = document.getElementById('emailfelt');
	if (! ( tlf && tlf.value.trim() ) ) {
	   msg += "\n" + 'Du har ikke udfyldt telefonnummer. Hvis du ikke har telefon, så skriv "ingen" i feltet.' + "\n";
	   res = false;	
        }
	if (! ( email && email.value.trim() ) ) {
	   msg += "\nDu har ikke udfyldt email.\n";
	   res = false;	
        }
	if (!res) {
	   alert(msg);
        }
	return res;
   }
</script>

</head>
<body>

<p>Hej <?=$user['navn'] ?></p>

<p>Vi vil lige sikre os, at de oplysninger, vi har om dig, er korrekte.</p>
<p>Kontrollér venligst oplysningerne herunder, og ret dem, der er forkerte eler mangler</p>



<form action="baadvalg.php" method="post" onsubmit="return check_felter()">
<?= $form_fields ?>
<table class="infotable">
<tr>
  <th>Navn</th>
  <td><?= $user['navn'] ?></td>
</tr>
<tr>
  <th>Medlemsnummer</th>
  <td><?= $user['ID'] ?></td>
</tr>
<tr>
  <th>Email</th>
  <td><input id="emailfelt" type="text" name="email" value="<?= htmlspecialchars($user['email'], ENT_COMPAT|ENT_HTML401|ENT_DISALLOWED) ?>" size="60" /></td>
</tr>
<tr>
  <th>Telefon</th>
  <td><input id="telefonfelt" type="text" name="tlf" value="<?= htmlspecialchars($user['tlf'], ENT_COMPAT|ENT_HTML401|ENT_DISALLOWED) ?>" size="15" /></td>
</tr>
<tr>
  <td colspan="2"><input type="submit" value="Gem" /></td>
</tr>
</table>
<input type="hidden" name="save_user_info" value="1" />
</form>

<?php

}
include("footer.php");
?>
