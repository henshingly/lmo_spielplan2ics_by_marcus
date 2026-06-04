<?php

/*

Diese Skript erstellt aus dem kopierten Spielplan einer LMO-Liga eine ICS-Datei

Voraussetzungen: 
1.  LMO
2.  PHP 5


Versionsübersicht:

Ver. 1.1  -  24.7.2014
•  Skriptoptimierung
•  Vereinsnamen können gekürzt werden (Infos siehe letzte Seite)

Ver. 1  -  23.7.2014
•  Grundfunktionen

*/


//error_reporting(E_ALL);
//error_reporting(0);  // auskommentiert - Fehler werden nicht mehr unterdr�ckt

echo '<!DOCTYPE HTML>
<html>
<head>
    <title>spielplan2ics</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  </head>
<body>
';

$seiteneu=true;
// Prüfen ob Seite neu angezeigt oder formular abgesendet
if (!isset($_POST['seiteneu']))
{

echo '<div>
<form id="formular" action="spielplan2ics.php" method="post">
  <div>Kopierten Text vom LMO-Spielplan einfügen:<br />
  <textarea name="kopierterspielplan" rows="30" cols="160"></textarea></div>
  <div><input type="submit" value="ICS-Datei erstellen" />
  <input type="hidden" name="seiteneu" value="1" /></div>
</form>
';

}  // if Seiteneu
else
{


$kopiertertext = $_POST['kopierterspielplan'];
//echo strlen($kopiertertext);

//$kopiertertext = substr($kopiertertext, strpos($kopiertertext, 'Spiel bearbeiten'));


/*echo '<pre>';
var_dump($kopiertertext);
echo '</pre>';
die;*/


$expo = explode ("\n",$kopiertertext);

/*echo '<pre>';
var_dump($expo);
echo '</pre>';
die;*/


$monat_max = 0;

$datei = fopen(__DIR__ . "/spielplan.ics", "w+");

fwrite($datei, 'BEGIN:VCALENDAR
PRODID:-//flaimo.com//iCal Class MIMEDIR//EN
VERSION:2.0
METHOD:REQUEST
');


//jede zeile ist ein Termin
for ($i=0; $i<count($expo); $i++) {  //-1 entfernt
  $spieltag = trim(substr($expo[$i], 0, 3));
  $datum_tmp = trim(substr($expo[$i], 3, 21));
  $datum = explode (".", $datum_tmp);
  //echo "- -". $datum[1];  // tag  
  //echo "- -". $datum[2];  // monat
  if ($datum[2] >= $monat_max) {
    $monat_max = $datum[2];
    $jahr = date("Y");
  }
  else {
    $jahr = date("Y") + 1;
  }
  //echo "- -" . $jahr;
  $zeit = explode (":", substr($datum_tmp, -5, 5));
  //echo "- -". ($zeit[0] + 1) . "-" . $zeit[1];    // +1 sommerzeit
  //echo "- -". $start = $jahr . $datum[2] . $datum[1] . "T" . ($zeit[0] + 1) . $zeit[1] . "00Z";
  //echo "- -". $ende = $jahr . $datum[2] . $datum[1] . "T" . ($zeit[0] + 3) . $zeit[1] . "00Z";  
  //echo "- -". trim(substr($expo[$i], 24, (strpos($expo[$i], "_   :   _")) - 24) );
  //echo "- -" .strpos($expo[$i], "_   :   _") - 24;
  
  //$spiel_tmp = str_replace('    -    ', ' - ', trim(substr($expo[$i], 24, (strpos($expo[$i], "_   :   _")) - 24) ) );
  //$spiel = str_replace("BC Erlbach 1919", "BCE", trim(substr($expo[$i], 24, (strpos($expo[$i], "_   :   _")) - 24) ) );
  
  $suchen = array("    -    ", "BC Erlbach 1919");
  $durchdasersetzen = array(" - ", "BCE");
  
  $spiel = str_replace($suchen, $durchdasersetzen, trim(substr($expo[$i], 24, (strpos($expo[$i], "_   :   _")) - 24) ));  

  //DTSTART:20120711T163000Z
  //DTEND:20120711T183000Z  
  $dtstamp = gmdate("Ymd\THis");  // UTC-Zeitstempel
  // Korrekte UTC-Konvertierung mit PHP-Zeitzonenfunktion
  date_default_timezone_set('Europe/Berlin');
  $ts_start = mktime((int)$zeit[0], (int)$zeit[1], 0, (int)$datum[2], (int)$datum[1], (int)$jahr);
  $ts_end   = $ts_start + 7200;  // +2 Stunden Spielzeit
  $dtstart  = gmdate("Ymd\THis\Z", $ts_start);
  $dtend    = gmdate("Ymd\THis\Z", $ts_end);

fwrite($datei, 'BEGIN:VEVENT
DTSTART:' . $dtstart . '
DTEND:' . $dtend . '
TRANSP:TRANSPARENT
SEQUENCE:0
UID:'.md5(uniqid('', true)).'
DTSTAMP:'.$dtstamp.'
CATEGORIES;LANGUAGE=de;ENCODING=QUOTED-PRINTABLE:Punktspiel '.$spieltag.'. Spieltag
DESCRIPTION;LANGUAGE=de;ENCODING=QUOTED-PRINTABLE:Punktspiel '.$spieltag.'. Spieltag
SUMMARY;LANGUAGE=de;ENCODING=QUOTED-PRINTABLE:'.$spiel.'
PRIORITY:5
CLASS:PUBLIC
URL:http://www.bcerlbach.de/
STATUS:CONFIRMED
END:VEVENT
');

  echo "<br>Termin ". $spieltag . ": "  . $spiel;

  
}  // for



fwrite($datei, "END:VCALENDAR");
fclose($datei);


echo '<br /><br />Ausgabe nach "spieplan.ics" erfolgt.';

}  // else Seiteneu

echo "</div>";
echo '</body>
</html>';


?>
