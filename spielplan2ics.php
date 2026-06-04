<?php

/*
Diese Skript erstellt aus dem kopierten Spielplan einer LMO-Liga eine ICS-Datei

Voraussetzungen:
1.  LMO
2.  PHP 8

Versionsübersicht:

Ver. 1.5  -  04.06.2026
•  PHP 8.x Kompatibilität: korrekte Zeitzonenbehandlung
•  __DIR__, uniqid('', true)
•  error_reporting auskommentiert
•  RFC 5545: METHOD:PUBLISH, CALSCALE:GREGORIAN, DTSTAMP mit Z-Suffix, ENCODING=QUOTED-PRINTABLE entfernt
•  Sonderzeichen-Escaping
•  eigene PRODID
•  UID mit Domain-Suffix
•  Eingabefeld für benutzerdefinierten ICS-Dateinamen
•  Direktlink zum Download der erzeugten ICS-Datei
•  Sprachauswahl für alle 14 LMO-Sprachen
•  Zeitzonenauswahl im Formular
•  PRODID, CATEGORIES, DESCRIPTION, SUMMARY LANGUAGE-Tags dynamisch
•  Komplette UI-Übersetzung in alle 14 LMO-Sprachen


Ver. 1.1  -  24.7.2014
•  Skriptoptimierung
•  Vereinsnamen können gekürzt werden (Infos siehe letzte Seite)

Ver. 1  -  23.7.2014
•  Grundfunktionen

*/

mb_internal_encoding("UTF-8");  // Korrekte Umlaut-Behandlung

//error_reporting(E_ALL);
//error_reporting(0);  // auskommentiert - Fehler werden nicht mehr unterdrückt

// Sprache und Zeitzone – aus POST (nach Submit) oder Default
$lmo_sprache  = isset($_POST['lmo_sprache'])  ? $_POST['lmo_sprache']  : 'de';
$lmo_timezone = isset($_POST['lmo_timezone']) ? $_POST['lmo_timezone'] : 'Europe/Berlin';

// Validierung
$erlaubte_sprachen  = array('de','en','fr','it','es','pt','nl','cs','hu','hr','bs','sl','ro','no');
$erlaubte_timezones = timezone_identifiers_list();
if (!in_array($lmo_sprache, $erlaubte_sprachen))   $lmo_sprache  = 'de';
if (!in_array($lmo_timezone, $erlaubte_timezones)) $lmo_timezone = 'Europe/Berlin';

// Wochentagnamen aller 14 LMO-Sprachen (längste zuerst)
$wochentage = array(
    'de' => array('Donnerstag','Dienstag','Mittwoch','Freitag','Samstag','Sonntag','Montag','Mo','Di','Mi','Do','Fr','Sa','So'),
    'en' => array('Wednesday','Thursday','Saturday','Tuesday','Monday','Friday','Sunday','Mon','Tue','Wed','Thu','Fri','Sat','Sun'),
    'fr' => array('Mercredi','Vendredi','Dimanche','Samedi','Lundi','Mardi','Jeudi','Lun','Mar','Mer','Jeu','Ven','Sam','Dim'),
    'it' => array('Mercoledì','Domenica','Martedì','Giovedì','Venerdì','Lunedì','Sabato','Lun','Mar','Mer','Gio','Ven','Sab','Dom'),
    'es' => array('Miércoles','Viernes','Domingo','Martes','Jueves','Sábado','Lunes','Lun','Mar','Mié','Jue','Vie','Sáb','Dom'),
    'pt' => array('Segunda','Domingo','Quarta','Quinta','Sábado','Terça','Sexta','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'),
    'nl' => array('Donderdag','Woensdag','Zaterdag','Maandag','Dinsdag','Vrijdag','Zondag','Ma','Di','Wo','Do','Vr','Za','Zo'),
    'cs' => array('Pondělí','Čtvrtek','Středa','Sobota','Neděle','Úterý','Pátek','Po','Út','St','Čt','Pá','So','Ne'),
    'hu' => array('Csütörtök','Vasárnap','Szombat','Szerda','Péntek','Hétfő','Kedd','Sze','Szo','Cs','H','K','P','V'),
    'hr' => array('Ponedjeljak','Četvrtak','Nedjelja','Srijeda','Utorak','Subota','Petak','Pon','Uto','Sri','Čet','Pet','Sub','Ned'),
    'bs' => array('Ponedjeljak','Četvrtak','Nedjelja','Srijeda','Utorak','Subota','Petak','Pon','Uto','Sri','Čet','Pet','Sub','Ned'),
    'sl' => array('Ponedeljek','Četrtek','Nedelja','Sobota','Torek','Sreda','Petek','Pon','Tor','Sre','Čet','Pet','Sob','Ned'),
    'ro' => array('Miercuri','Duminică','Sâmbătă','Vineri','Marți','Luni','Joi','Lu','Ma','Mi','Jo','Vi','Sâ','Du'),
    'no' => array('Tirsdag','Torsdag','Mandag','Onsdag','Fredag','Lørdag','Søndag','Man','Tir','Ons','Tor','Fre','Lør','Søn'),
);
$aktuelle_wochentage = $wochentage[$lmo_sprache];

// UI-Texte in allen 14 LMO-Sprachen
$ui_texte = array(
    'de' => array(
        'form_title'      => 'Kopierten Text vom LMO-Spielplan einf&uuml;gen:',
        'label_sprache'   => 'Sprache des LMO:',
        'label_timezone'  => 'Zeitzone:',
        'label_dateiname' => 'Dateiname der ICS-Datei:',
        'btn_erstellen'   => 'ICS-Datei erstellen',
        'output_termin'   => 'Termin',
        'output_erfolg'   => 'ICS-Datei erstellt:',
        'output_download' => 'herunterladen',
        'tooltip'         => 'Kein Dateiname mit Sonderzeichen wie \\ / : * ? &lt; &gt; | &quot;',
    ),
    'en' => array(
        'form_title'      => 'Paste copied LMO schedule text here:',
        'label_sprache'   => 'LMO language:',
        'label_timezone'  => 'Timezone:',
        'label_dateiname' => 'ICS file name:',
        'btn_erstellen'   => 'Create ICS file',
        'output_termin'   => 'Event',
        'output_erfolg'   => 'ICS file created:',
        'output_download' => 'download',
        'tooltip'         => 'No special characters like \\ / : * ? &lt; &gt; | &quot; in filename',
    ),
    'fr' => array(
        'form_title'      => 'Coller le texte copi&eacute; du calendrier LMO&nbsp;:',
        'label_sprache'   => 'Langue LMO&nbsp;:',
        'label_timezone'  => 'Fuseau horaire&nbsp;:',
        'label_dateiname' => 'Nom du fichier ICS&nbsp;:',
        'btn_erstellen'   => 'Cr&eacute;er le fichier ICS',
        'output_termin'   => '&Eacute;v&eacute;nement',
        'output_erfolg'   => 'Fichier ICS cr&eacute;&eacute;&nbsp;:',
        'output_download' => 't&eacute;l&eacute;charger',
        'tooltip'         => 'Pas de caract&egrave;res sp&eacute;ciaux comme \\ / : * ? &lt; &gt; | &quot;',
    ),
    'it' => array(
        'form_title'      => 'Incolla il testo del calendario LMO copiato:',
        'label_sprache'   => 'Lingua LMO:',
        'label_timezone'  => 'Fuso orario:',
        'label_dateiname' => 'Nome del file ICS:',
        'btn_erstellen'   => 'Crea file ICS',
        'output_termin'   => 'Evento',
        'output_erfolg'   => 'File ICS creato:',
        'output_download' => 'scarica',
        'tooltip'         => 'Nessun carattere speciale come \\ / : * ? &lt; &gt; | &quot;',
    ),
    'es' => array(
        'form_title'      => 'Pegar el texto del calendario LMO copiado:',
        'label_sprache'   => 'Idioma LMO:',
        'label_timezone'  => 'Zona horaria:',
        'label_dateiname' => 'Nombre del archivo ICS:',
        'btn_erstellen'   => 'Crear archivo ICS',
        'output_termin'   => 'Evento',
        'output_erfolg'   => 'Archivo ICS creado:',
        'output_download' => 'descargar',
        'tooltip'         => 'Sin caracteres especiales como \\ / : * ? &lt; &gt; | &quot;',
    ),
    'pt' => array(
        'form_title'      => 'Colar o texto do calend&aacute;rio LMO copiado:',
        'label_sprache'   => 'Idioma LMO:',
        'label_timezone'  => 'Fuso hor&aacute;rio:',
        'label_dateiname' => 'Nome do arquivo ICS:',
        'btn_erstellen'   => 'Criar arquivo ICS',
        'output_termin'   => 'Evento',
        'output_erfolg'   => 'Arquivo ICS criado:',
        'output_download' => 'baixar',
        'tooltip'         => 'Sem caracteres especiais como \\ / : * ? &lt; &gt; | &quot;',
    ),
    'nl' => array(
        'form_title'      => 'Plak de gekopieerde LMO-speelplantekst hier:',
        'label_sprache'   => 'LMO-taal:',
        'label_timezone'  => 'Tijdzone:',
        'label_dateiname' => 'Naam van het ICS-bestand:',
        'btn_erstellen'   => 'ICS-bestand aanmaken',
        'output_termin'   => 'Afspraak',
        'output_erfolg'   => 'ICS-bestand aangemaakt:',
        'output_download' => 'downloaden',
        'tooltip'         => 'Geen speciale tekens zoals \\ / : * ? &lt; &gt; | &quot;',
    ),
    'cs' => array(
        'form_title'      => 'Vlo&#382;te zkop&iacute;rovan&yacute; text rozvrhu LMO:',
        'label_sprache'   => 'Jazyk LMO:',
        'label_timezone'  => '&#268;asov&eacute; p&aacute;smo:',
        'label_dateiname' => 'N&aacute;zev souboru ICS:',
        'btn_erstellen'   => 'Vytvo&#345;it soubor ICS',
        'output_termin'   => 'Ud&aacute;lost',
        'output_erfolg'   => 'Soubor ICS vytvo&#345;en:',
        'output_download' => 'st&aacute;hnout',
        'tooltip'         => '&#381;&aacute;dn&eacute; speci&aacute;ln&iacute; znaky jako \\ / : * ? &lt; &gt; | &quot;',
    ),
    'hu' => array(
        'form_title'      => 'Illessze be az LMO menetrend m&aacute;solt sz&ouml;veg&eacute;t:',
        'label_sprache'   => 'LMO nyelv:',
        'label_timezone'  => 'Id&#337;z&oacute;na:',
        'label_dateiname' => 'ICS f&aacute;jl neve:',
        'btn_erstellen'   => 'ICS f&aacute;jl l&eacute;trehoz&aacute;sa',
        'output_termin'   => 'Esem&eacute;ny',
        'output_erfolg'   => 'ICS f&aacute;jl l&eacute;trehozva:',
        'output_download' => 'let&ouml;lt&eacute;s',
        'tooltip'         => 'Nincs speci&aacute;lis karakter, p&eacute;ld&aacute;ul \\ / : * ? &lt; &gt; | &quot;',
    ),
    'hr' => array(
        'form_title'      => 'Zalijepite kopirani tekst rasporeda LMO:',
        'label_sprache'   => 'Jezik LMO:',
        'label_timezone'  => 'Vremenska zona:',
        'label_dateiname' => 'Naziv ICS datoteke:',
        'btn_erstellen'   => 'Stvori ICS datoteku',
        'output_termin'   => 'Doga&#273;aj',
        'output_erfolg'   => 'ICS datoteka stvorena:',
        'output_download' => 'preuzmi',
        'tooltip'         => 'Bez posebnih znakova poput \\ / : * ? &lt; &gt; | &quot;',
    ),
    'bs' => array(
        'form_title'      => 'Zalijepite kopirani tekst rasporeda LMO:',
        'label_sprache'   => 'Jezik LMO:',
        'label_timezone'  => 'Vremenska zona:',
        'label_dateiname' => 'Naziv ICS datoteke:',
        'btn_erstellen'   => 'Kreiraj ICS datoteku',
        'output_termin'   => 'Doga&#273;aj',
        'output_erfolg'   => 'ICS datoteka kreirana:',
        'output_download' => 'preuzmi',
        'tooltip'         => 'Bez posebnih znakova poput \\ / : * ? &lt; &gt; | &quot;',
    ),
    'sl' => array(
        'form_title'      => 'Prilepite kopirano besedilo urnika LMO:',
        'label_sprache'   => 'Jezik LMO:',
        'label_timezone'  => '&#268;asovni pas:',
        'label_dateiname' => 'Ime datoteke ICS:',
        'btn_erstellen'   => 'Ustvari datoteko ICS',
        'output_termin'   => 'Dogodek',
        'output_erfolg'   => 'Datoteka ICS ustvarjena:',
        'output_download' => 'prenesi',
        'tooltip'         => 'Brez posebnih znakov kot \\ / : * ? &lt; &gt; | &quot;',
    ),
    'ro' => array(
        'form_title'      => 'Lipi&#539;i textul copiat al programului LMO:',
        'label_sprache'   => 'Limba LMO:',
        'label_timezone'  => 'Fus orar:',
        'label_dateiname' => 'Numele fi&#351;ierului ICS:',
        'btn_erstellen'   => 'Crea&#539;i fi&#351;ierul ICS',
        'output_termin'   => 'Eveniment',
        'output_erfolg'   => 'Fi&#351;ier ICS creat:',
        'output_download' => 'descarc&aacute;',
        'tooltip'         => 'F&aacute;r&aacute; caractere speciale ca \\ / : * ? &lt; &gt; | &quot;',
    ),
    'no' => array(
        'form_title'      => 'Lim inn kopiert LMO-spilleplanstekst her:',
        'label_sprache'   => 'LMO-spr&aring;k:',
        'label_timezone'  => 'Tidssone:',
        'label_dateiname' => 'Navn p&aring; ICS-fil:',
        'btn_erstellen'   => 'Opprett ICS-fil',
        'output_termin'   => 'Hendelse',
        'output_erfolg'   => 'ICS-fil opprettet:',
        'output_download' => 'last ned',
        'tooltip'         => 'Ingen spesialtegn som \\ / : * ? &lt; &gt; | &quot; i filnavnet',
    ),
);
$t = $ui_texte[$lmo_sprache];

echo '<!DOCTYPE HTML>
<html>
<head>
    <title>spielplan2ics</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  </head>
<body>
';

$seiteneu = true;
// Prüfen ob Seite neu angezeigt oder Formular abgesendet
if (!isset($_POST['seiteneu']) || isset($_POST['nur_sprache']) && $_POST['nur_sprache'] === '1') {
    echo '<div>
    <form id="formular" action="spielplan2ics.php" method="post">
      <div>' . $t['form_title'] . '<br />
        <textarea name="copied_schedule" rows="30" cols="160"></textarea>
      </div>
      <div style="margin-top:8px;">
        ' . $t['label_sprache'] . '
        <select name="lmo_sprache" onchange="document.getElementById(&quot;nur_sprache&quot;).value=&quot;1&quot;; this.form.submit();">
          <option value="de"' . ($lmo_sprache=='de' ? ' selected' : '') . '>Deutsch</option>
          <option value="en"' . ($lmo_sprache=='en' ? ' selected' : '') . '>Englisch</option>
          <option value="fr"' . ($lmo_sprache=='fr' ? ' selected' : '') . '>Fran&ccedil;ais</option>
          <option value="it"' . ($lmo_sprache=='it' ? ' selected' : '') . '>Italiano</option>
          <option value="es"' . ($lmo_sprache=='es' ? ' selected' : '') . '>Espa&ntilde;ol</option>
          <option value="pt"' . ($lmo_sprache=='pt' ? ' selected' : '') . '>Portugu&ecirc;s</option>
          <option value="nl"' . ($lmo_sprache=='nl' ? ' selected' : '') . '>Nederlands</option>
          <option value="cs"' . ($lmo_sprache=='cs' ? ' selected' : '') . '>&#268;e&scaron;tina</option>
          <option value="hu"' . ($lmo_sprache=='hu' ? ' selected' : '') . '>Magyar</option>
          <option value="hr"' . ($lmo_sprache=='hr' ? ' selected' : '') . '>Hrvatski</option>
          <option value="bs"' . ($lmo_sprache=='bs' ? ' selected' : '') . '>Bosanski</option>
          <option value="sl"' . ($lmo_sprache=='sl' ? ' selected' : '') . '>Sloven&scaron;&#269;ina</option>
          <option value="ro"' . ($lmo_sprache=='ro' ? ' selected' : '') . '>Rom&acirc;n&#259;</option>
          <option value="no"' . ($lmo_sprache=='no' ? ' selected' : '') . '>Norsk</option>
        </select>
      </div>
      <div style="margin-top:8px;">
        ' . $t['label_timezone'] . '
        <select name="lmo_timezone" onchange="document.getElementById(&quot;nur_sprache&quot;).value=&quot;1&quot;; this.form.submit();">
          <option value="Europe/Berlin"'   . ($lmo_timezone=='Europe/Berlin'    ? ' selected' : '') . '>Europe/Berlin (Deutschland, UTC+1/+2)</option>
          <option value="Europe/London"'   . ($lmo_timezone=='Europe/London'    ? ' selected' : '') . '>Europe/London (UK/Irland, UTC+0/+1)</option>
          <option value="Europe/Paris"'    . ($lmo_timezone=='Europe/Paris'     ? ' selected' : '') . '>Europe/Paris (Frankreich, UTC+1/+2)</option>
          <option value="Europe/Rome"'     . ($lmo_timezone=='Europe/Rome'      ? ' selected' : '') . '>Europe/Rome (Italien, UTC+1/+2)</option>
          <option value="Europe/Madrid"'   . ($lmo_timezone=='Europe/Madrid'    ? ' selected' : '') . '>Europe/Madrid (Spanien, UTC+1/+2)</option>
          <option value="Europe/Lisbon"'   . ($lmo_timezone=='Europe/Lisbon'    ? ' selected' : '') . '>Europe/Lisbon (Portugal, UTC+0/+1)</option>
          <option value="Europe/Amsterdam"'. ($lmo_timezone=='Europe/Amsterdam' ? ' selected' : '') . '>Europe/Amsterdam (Niederlande, UTC+1/+2)</option>
          <option value="Europe/Prague"'   . ($lmo_timezone=='Europe/Prague'    ? ' selected' : '') . '>Europe/Prague (Tschechien, UTC+1/+2)</option>
          <option value="Europe/Budapest"' . ($lmo_timezone=='Europe/Budapest'  ? ' selected' : '') . '>Europe/Budapest (Ungarn, UTC+1/+2)</option>
          <option value="Europe/Zagreb"'   . ($lmo_timezone=='Europe/Zagreb'    ? ' selected' : '') . '>Europe/Zagreb (Kroatien, UTC+1/+2)</option>
          <option value="Europe/Sarajevo"' . ($lmo_timezone=='Europe/Sarajevo'  ? ' selected' : '') . '>Europe/Sarajevo (Bosnien, UTC+1/+2)</option>
          <option value="Europe/Ljubljana"'. ($lmo_timezone=='Europe/Ljubljana' ? ' selected' : '') . '>Europe/Ljubljana (Slowenien, UTC+1/+2)</option>
          <option value="Europe/Bucharest"'. ($lmo_timezone=='Europe/Bucharest' ? ' selected' : '') . '>Europe/Bucharest (Rum&auml;nien, UTC+2/+3)</option>
          <option value="Europe/Oslo"'     . ($lmo_timezone=='Europe/Oslo'      ? ' selected' : '') . '>Europe/Oslo (Norwegen, UTC+1/+2)</option>
          <option value="UTC"'             . ($lmo_timezone=='UTC'              ? ' selected' : '') . '>UTC (Koordinierte Weltzeit)</option>
        </select>
      </div>
      <div style="margin-top:8px;">
        ' . $t['label_dateiname'] . '
        <input type="text" name="ics_filename" value="spielplan" size="40"
          title="' . $t['tooltip'] . '" />.ics
      </div>
      <div style="margin-top:8px;">
        <input type="submit" value="' . $t['btn_erstellen'] . '">
        <input type="hidden" name="seiteneu" value="1">
        <input type="hidden" id="nur_sprache" name="nur_sprache" value="">
      </div>
    </form>
';

} else {
    $copied_text = $_POST['copied_schedule'];
    $expo = explode ("\n", $copied_text);
    $monat_max = 0;

    // Dateiname aus Formular
    $ics_filename = isset($_POST['ics_filename']) ? trim($_POST['ics_filename']) : 'spielplan';
    $ics_filename = preg_replace('#[\\/: *?<>|"]+#', '-', $ics_filename);
    $ics_filename = preg_replace('/\.ics$/i', '', $ics_filename);
    if ($ics_filename === '') $ics_filename = 'spielplan';
    $ics_dateipfad = __DIR__ . '/' . $ics_filename . '.ics';
    $datei = fopen($ics_dateipfad, "w+");
    $lang_upper = strtoupper($lmo_sprache);
    fwrite($datei, 'BEGIN:VCALENDAR
PRODID:-//Liga Manager Online//spielplan2ics//' . $lang_upper . '
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
');


    //   jede Zeile ist ein Termin
    //   Unterstützte Datumsformate (DateTimeInterface::format des LMO-Administrators):
    //   TT.MM.YYYY   TT.MM.YY   TT.MM.   j.n.Y   j.n.   YYYY-MM-DD
    //   Optional mit Wochentag-Prefix: "Fr 22.08.2025" oder "Freitag 22.08.2025"
    //   Optional mit Uhrzeit: "22.08.2025 20:30" oder ohne Uhrzeit
    date_default_timezone_set($lmo_timezone);

    // Hilfsfunktion: Datum aus beliebigem LMO-Format parsen
    // Gibt Array mit [tag, monat, jahr, stunde, minute] zurück oder false
    function parseLMODatum($datum_raw, &$monat_max, &$basis_jahr, $wochentage_liste) {
        // Wochentag-Prefix entfernen (sprachspezifisch, längste zuerst)
        $datum_clean = trim($datum_raw);
        foreach ($wochentage_liste as $wt) {
            if (mb_stripos($datum_clean, $wt) === 0) {
                $datum_clean = trim(mb_substr($datum_clean, mb_strlen($wt)));
                break;
            }
        }

        $tag = $monat = $jahr = $stunde = $minute = null;

        // Uhrzeit extrahieren (HH:MM oder H:MM am Ende)
        if (preg_match('/(\d{1,2}):(\d{2})\s*$/', $datum_clean, $tz)) {
            $stunde  = (int)$tz[1];
        $minute  = (int)$tz[2];
        $datum_clean = trim(substr($datum_clean, 0, strrpos($datum_clean, $tz[0])));
        } else {
            $stunde = 0;
            $minute = 0;
        }

        // Format YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($datum_clean), $dm)) {
            $jahr  = (int)$dm[1];
            $monat = (int)$dm[2];
            $tag   = (int)$dm[3];
        }
        // Format TT.MM.YYYY oder T.M.YYYY
        elseif (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', trim($datum_clean), $dm)) {
            $tag   = (int)$dm[1];
            $monat = (int)$dm[2];
            $jahr  = (int)$dm[3];
        }
        // Format TT.MM.YY oder T.M.YY (2-stelliges Jahr)
        elseif (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2})$/', trim($datum_clean), $dm)) {
            $tag   = (int)$dm[1];
            $monat = (int)$dm[2];
            $jahr  = 2000 + (int)$dm[3];
        }
        // Format TT.MM. oder T.M. (kein Jahr -> aus Monatsverlauf ableiten)
        elseif (preg_match('/^(\d{1,2})\.(\d{1,2})\.$/', trim($datum_clean), $dm)) {
            $tag   = (int)$dm[1];
            $monat = (int)$dm[2];
            if ($monat >= $monat_max) {
                $monat_max = $monat;
                $jahr = $basis_jahr;
            } else {
                $basis_jahr++;
                $monat_max = $monat;
                $jahr = $basis_jahr;
            }
        }
        else {
            return false; // Format nicht erkannt
        }

        return array($tag, $monat, $jahr, $stunde, $minute);
    }

    $monat_max  = 0;
    $basis_jahr = (int)date('Y');

    for ($i=0; $i<count($expo); $i++) {
        // Felder per Tab trennen
        $cols = explode("\t", $expo[$i]);
        if (count($cols) < 9) continue; // Zeile hat nicht genug Felder

        $spieltag_raw = trim($cols[0]);
        $datum_raw    = trim($cols[2]);
        $heim_raw     = trim($cols[4]);
        $gast_raw     = trim($cols[6]); // nach dem "- " Separator

        // Spieltagnummer extrahieren
        if (!preg_match('/(\d+)/', $spieltag_raw, $sm)) continue;
        $spieltag = (int)$sm[1];

        // Datum parsen
        $datumParts = parseLMODatum($datum_raw, $monat_max, $basis_jahr, $aktuelle_wochentage);
        if ($datumParts === false) continue;
        list($tag, $monat, $jahr, $stunde, $minute) = $datumParts;

        // Teamnamen bereinigen
        $heim = preg_replace('/\s+/', ' ', $heim_raw);
        $gast = preg_replace('/\s+/', ' ', $gast_raw);

        // Vereinsnamen kürzen
        $suchen          = array("BC Erlbach 1919");
        $durchdasersetzen = array("BCE");
        $heim  = str_replace($suchen, $durchdasersetzen, $heim);
        $gast  = str_replace($suchen, $durchdasersetzen, $gast);
        $spiel = $heim . ' - ' . $gast;

        $dtstamp  = gmdate("Ymd\THis\Z");
        $ts_start = mktime($stunde, $minute, 0, $monat, $tag, $jahr);
        $ts_end   = $ts_start + 7200;  // +2 Stunden Spielzeit
        $dtstart  = gmdate("Ymd\THis\Z", $ts_start);
        $dtend    = gmdate("Ymd\THis\Z", $ts_end);

        // Sonderzeichen in Teamnamen escapen (RFC 5545)
        $spiel_ics = str_replace(array('\\', ';', ','), array('\\\\', '\;', '\,'), $spiel);

        fwrite($datei, 'BEGIN:VEVENT
DTSTART:' . $dtstart . '
DTEND:' . $dtend . '
TRANSP:TRANSPARENT
SEQUENCE:0
UID:'.md5(uniqid('', true)).'@spielplan2ics
DTSTAMP:'.$dtstamp.'
CATEGORIES;LANGUAGE=' . $lmo_sprache . ':Punktspiel
DESCRIPTION;LANGUAGE=' . $lmo_sprache . ':' . $spieltag . '. Spieltag
SUMMARY;LANGUAGE=' . $lmo_sprache . ':' . $spiel_ics . '
PRIORITY:5
CLASS:PUBLIC
URL:https://www.liga-manager-online.org/
STATUS:CONFIRMED
END:VEVENT
');
        echo '<br>' . $t['output_termin'] . ' ' . $spieltag . ': ' . $spiel;
    }  // Ende for
    fwrite($datei, "END:VCALENDAR");
    fclose($datei);
    $ics_url = basename($ics_dateipfad);
    echo '<br /><br />' . $t['output_erfolg'] . ' '
        . '<a href="' . htmlspecialchars($ics_url) . '" download="' . htmlspecialchars($ics_filename) . '.ics">'
        . '&#x1F4C5; ' . htmlspecialchars($ics_filename) . '.ics &ndash; ' . $t['output_download'] . '</a>';
}  // Ende else
echo '  </div>
</body>
</html>';
?>
