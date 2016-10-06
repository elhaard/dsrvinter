<?php


$mail_content="Hej roer\n\n".
    "For at ønske et bådhold skal du bruge nedenstående link:\n".
    "https://www.nversion.dk/dsrvinter/tilmeld3.php?email=$email&kode=$kode\n\n".
    "Hvis linket ikke virker skal du gå til siden:\n".
    "https://www.nversion.dk/dsrvinter/tilmeld3.php og indtaste\n".
    "    Login $email\n".
    "    Kode $kode\n\n".
    "Har du problemer med at logge ind, så prøv at bestil en ny kode.\n\n".
    "Gem både link og kode da dette vil være din adgang til systemet.\n\n".
    "Tilmeldingen skal betragtes som en ønskeliste og materieludvalget forbeholder sig ret til at flytte medlemmer til et andet bådhold. Det er muligt at bruge ovenstående link til at tilmelde sig et andet hold indtil 31/9. Herefter vil tilmeldingen være bindende.\n\n".
    "Med venlig hilsen\n".
    "Materieludvalget";

$mail_subject="Tilmelding til DSR vintervedligehold\n";

$mail_headers=array();
$mail_headers['From'] = "DSR Materieludvalg - svar ikke! <web1@nversion.dk>";
$mail_headers['Content-Transfer-Encoding'] = "8bit";
$mail_headers['Date'] = "".date('r');
$mail_headers['Message-ID'] = "<".sha1(microtime(true))."@web1.nversion.dk>";
$mail_headers['MIME-Version'] = "1.0";
$mail_headers['X-Mailer'] = "PHP-Custom";
$mail_headers['Subject'] = "$mail_subject";


?>
