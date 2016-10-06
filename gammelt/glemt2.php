<?php
require_once("Mail.php");
include_once("header.php");

if(isset($_POST["email"])) {
  $email=addslashes($_POST["email"]);
  //  $medlemnr=addslashes($_POST["medlemnr"]);
  $sql="SELECT *  FROM `dsr_vinter_person` WHERE `email` = '$email' LIMIT 1";
  $res=mysql_query($sql,$link);
  $row=mysql_fetch_row($res);

  if (DEBUG) {
    echo "<pre>\n";
    print_r ($row);
    echo "</pre><br>\n";
  }
    
  $nu=time();

  if(isset($row[8])) {
    // no error = email fandtes
    $kode=$row[8];
    if (DEBUG) {echo "Fandtes allerede. kode: $kode\n"; }
  } else {
    $nu_micro="".microtime();
    $kode=crypt($nu_micro,"DSR");
    $kode=rand(10000,99999);
    if (DEBUG) {echo "Ny bruger = ny kode\n";}
  }

  $result = mysql_query("update `dsr_vinter_person` set `email`='$email',`email_sent`=$nu,`kode`='$kode'  where `email` like '$email';");		 
  if (mysql_affected_rows()==0) {
    // if not exist, insert instead
    $result = mysql_query("INSERT INTO `dsr_vinter_person` (`email` , `email_sent` , `kode` ) VALUES ('$email', $nu, '$kode')");
  }
  
  include("settings.php");
  if (DEBUG) {
    echo "<br>email: $email<br>\n";
    echo "mail_subject: $mail_subject<br>\n";
    //   echo "mail_content: <br>\n<pre>$mail_content</pre>\n";
    //echo "mail_header: <br><pre>".htmlentities($mail_headers)."</pre>\n";
  }

  //$mail_status= mail($email,$mail_subject,$mail_content,$mail_headers);

  $smtp = Mail::factory('smtp',
   array ('host' => 'localhost',
          'port' => 25,
     'auth' => false));
 
  $mail_headers['To'] = $email;
  $mail_status = $smtp->send($email, $mail_headers, $mail_content);
 
  if (PEAR::isError($mail_status)) {
   error_log("Mail to " . $email . " Not sent successfully. Message was: " . $mail_status->getMessage() . "");
  } 

  if (DEBUG) {
    echo "<table>\n";
    echo "<tr><td>email</td><td><tt>$email</tt></td></tr>\n";
    echo "<tr><td>email_sent</td><td><tt>$nu</tt></td></tr>\n";
    echo "<tr><td>kode</td><td><tt>$kode</tt></td></tr>\n";
    echo "<tr><td>mail_status</td><td>";
    if (!PEAR::isError($mail_status)) {
      echo "<tt>OK</tt>";
    } else {
      echo "<tt>FEJL</tt>";
    }
    echo "</td></tr>\n";
    echo "</table>\n";
  }
  
 }
?>



</head>
<body>
<h2>Vi har sendt dig en kode..</h2>
<p>Vi har sendt en kode til <tt><?php echo $email; ?></tt>.<br>Check, at du har skrevet din email korrekt.</p>
<p>I den email vi har sendt står hvordan du ønsker hvilken båd du skal vintervedligeholde.</p>
<p>Hvis e-mailen er forkert eller hvis du ikke modtager en mail fra os i løbet af kort tid, <a href='index.php'>prøv da venligst igen</a>.
<p>Herunder kan du se en liste med alle både, og hvem/hvormange der allerede er tilmeldt. Hvis en båd er optaget vil det ikke være muligt at vælge den. Find derfor gerne et par alternativer.</p>
<p>Med venlig hilsen<br />
Materieludvalget</p>

<?php
include("list_baade2.php");
include("footer.php");
?>
