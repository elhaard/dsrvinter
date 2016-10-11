<?php
include("../inc/csvParse.php");


$input1 = <<<'DOC1'
fisk;nisse;"Banan";Min \"fede\" ferie;"Gunnar \"Nu\" Hansen"
hund;smølf;\\;"\\";\";
;;
Skift;over;"Flere
linier";"Med
 meget 
mere"
Klaphat
DOC1;

$input2 = <<<'DOC2'
fisk;nisse;"Banan";Min ""fede"" ferie;"Gunnar ""Nu"" Hansen"
hund;smølf;\;\;""""
;;

Klaphat
DOC2;

var_dump(csvParse($input1, ";", '"', "\\"));

var_dump(csvParse($input2, ';', '"', false));


?>
