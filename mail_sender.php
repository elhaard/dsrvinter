<?php
  require_once("Mail.php");
  function send_email( $subject, $template, $user ) {
      $smtp = Mail::factory('smtp',
          array ('host' => 'chimay.elgaard.net',
          'port' => 26,
          'auth' => true,
          'username' => 'sasltest',
          'password' => 'ged'));

      $res = false;
      if (isset($user['email']) && trim($user['email'])) {

          $email = trim($user['email']);

          $mail_headers = array(
                              'From'                      => "DSR Materieludvalg - svar ikke! <jel@elgaard.net>",
                              'Content-Transfer-Encoding' => "8bit",
			      'Content-Type'              => 'text/plain; charset="utf8"',
			      'Date'                      => date('r'),
			      'Message-ID'                => "<".sha1(microtime(true))."@web1.nversion.dk>",
                              'MIME-Version'              => "1.0",
                              'X-Mailer'                  => "PHP-Custom",
                              'Subject'                   => "$subject"
                               );

          $mail_headers['To'] = $email;

          $mail_content = preg_replace_callback(
                              '/%([a-zA-Z0-9_-]+)%/',
                              function ($m) use ($user) {
                                 return $user[$m[1]];
                              },
                              $template
                         );

          $mail_status = $smtp->send($email, $mail_headers, $mail_content);

          if (PEAR::isError($mail_status)) {
   	      $res = "Kunne ikke sende mail til $email: " . $mail_status->getMessage();
          }
     } else {
        $res = "Medlemsnummer " . $user['ID'] . " har ingen email-adresse!";
     }

     return $res;
  }

?>

