<?php

if (isset($_GET["til"])) {
  $to=$_GET["til"];
  
  for ($i=1;$i<24;$i++) {
    $sent=mail ($to, "Dette er mail nummer $i", "Kryds fingrer...", "From: DSR vintergejl <no-reply@dsr-butik.dk>");
    if ($sent) {
      echo "#$i<br>\n";
    } else {
      echo "Hov!!! Noget gik galt med #$i! [<pre>$sent</pre>]<br>\n";
    }
    unset($sent);
    flush();
  }
  mail($to,"Det her er den sidste mail","Du skulle gerne have fået en række fortløbende mails..? Hvis du har, så virker lortet sgu. Hvis ikke, så er der noget galt!\n","From: no-reply@kbnielsen.dk");
 } else {
  echo "Du skal vide, hvordan du bruger den her side...\n";
 }
?>
