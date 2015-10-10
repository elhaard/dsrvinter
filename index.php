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
<h1>Tilmelding til vintervedligehold er &aring;ben</h1>
<!--
<p>Hvis du ikke nåede at få dig tilmeldt, så er der ikke så meget andet at gøre end at vente og se, hvilken båd du er kommet på.</p>
-->

<p>Her kan du tilmelde dig vintervedligehold:</p>
Hvis man glemmer at melde sig til vil man blive placeret på et vilk&aring;rligt b&aring;dhold.</p>

<p>For at logge ind, skal du bruge et særligt password, som du har fået tilsendt pr. mail.<br/>
Hvis du ikke har modtaget dit password (eller ikke kan huske det) så kan du <a href="glemt.php">klikke her for at få det tilsendt</a>.
</p>

<br />
<form action="tilmeld2.php" method="POST">
  <table border="0" class="login-boks">
     <tr>
	 <th colspan="2">Login</th>
     </tr>
     <tr>
	<td>Medlemsnummer:</td>
	<td><input type='text' name='medlemsnummer' size='4'></td>
    </tr>
    <tr>
	<td>Vinter-password:</td>
	<td><input type="password" name="password" size="20"></td>
    </tr>
    <tr>
	<td colspan="2"><input type='submit' value='Login...'></td>
    </tr>
  </table>
</form>
<a href="glemt.php">Glemt password?</a>
</body>
</html>
