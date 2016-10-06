<?php
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once("inc/db.php");
include_once("inc/mail_sender.php");
$result = array();

if (isset($_POST["medlemsnummer"]) && isset($_POST["password"])){
  $medlemsnummer=trim($_POST["medlemsnummer"]);  
  $kode=trim($_POST["password"]);

  $res = $link->query("SELECT * FROM dsr_vinter_person WHERE ID = " . (int) $medlemsnummer . " AND kode = '" . $link->escape_string($kode) . "'");
  if ($res) {
    if ($res->num_rows == 1) {
      $user = $res->fetch_assoc();
    }
    $res->close();
  }
}

if (isset($user) && $user['is_admin']) {
   if (isset($_POST['recipient']) &&
       isset($_POST['subject']) &&
       isset($_POST['template']) &&
       $_POST['recipient'] &&
       $_POST['subject'] &&
       $_POST['template']
    ) {
     $recipient = $_POST['recipient'];
     $subject = $_POST['subject'];
     $template = $_POST['template'];
     $res = $link->query("SELECT * FROM dsr_vinter_person WHERE ID = " . (int) $recipient);
     if ($res) {
	if ($res->num_rows == 1) {
           $row = $res->fetch_assoc();
           $template = get_setting($template);
           $mail_error = send_email($subject, $template, $row);
           if ($mail_error) {
               $result['error'] = $mail_error;
           } else {
               if (isset($_POST['mark_sent']) && $_POST['mark_sent']) {
		  $link->query("UPDATE dsr_vinter_person SET email_sent = 1 WHERE ID = " . (int) $row['ID']);
               }
               $result['success'] = true;
           }
           $res->close();
       } else {
          $result['error'] = "Kunne ikke finde medlemsnummer '$recipient'";
       }
    } else {
       $result['error'] = "Databasefejl: " . $link->error;
    }
  } else {
    $result['error'] = 'Ikke nok oplysninger';
  }
} else {
  $result['error'] = "Du er ikke administrator";
}

echo json_encode($result);
?>
