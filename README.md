# spielplan2ics

**Addon für Liga Manager Online (LMO)**
Erstellt von Marcus | Version 1.1 (24.07.2014)

---

## Was macht dieses Addon?

`spielplan2ics.php` wandelt einen kopierten LMO-Spielplan in eine ICS-Kalenderdatei (`spielplan.ics`) um, die in gängige Kalenderanwendungen wie Google Calendar, Apple Calendar oder Outlook importiert werden kann.

---

## Voraussetzungen (laut Originaldokumentation)

1. Eine laufende LMO-Installation
2. PHP 5 (Hinweis: Kompatibilitätshinweise für PHP 8.x siehe unten)

---

## Bedienung

### Schritt 1 — Im LMO
- Die gewünschte Liga im LMO öffnen
- Unter **Spielpläne** die gewünschte Mannschaft auswählen
- Die Maus muss **vor dem 1. Spieltag** positioniert werden
- Den gesamten Spielplan mit der Maus markieren und in die Zwischenablage kopieren

### Schritt 2 — In spielplan2ics.php
- Das Script im Browser aufrufen (direkt, nicht über LMO)
- Den kopierten Text aus der Zwischenablage in das Textfeld einfügen
- Auf **"ICS-Datei erstellen"** klicken
- Die erzeugte Datei `spielplan.ics` wird im selben Verzeichnis wie das Script gespeichert

### Schritt 3 — Kalender importieren
- Die erzeugte `spielplan.ics` in den gewünschten Kalender importieren (Google Calendar, Apple Calendar, Outlook etc.)

---

## Vereinsnamen kürzen

Im Script können Vereinsnamen durch Kurzformen ersetzt werden. Das ist über die Arrays `$suchen` und `$durchdasersetzen` konfigurierbar:

```php
$suchen          = array("    -    ", "BC Erlbach 1919");
$durchdasersetzen = array(" - ",      "BCE");
```

Weitere Einträge können nach dem gleichen Muster ergänzt werden.

---

## Installation

1. `spielplan2ics.php` in ein beliebiges, über den Webserver erreichbares Verzeichnis kopieren
2. Das Verzeichnis muss **Schreibrechte** haben, damit `spielplan.ics` erstellt werden kann
3. Script direkt im Browser aufrufen (z.B. `https://example.com/tools/spielplan2ics.php`)

Das Script ist **unabhängig vom LMO-Verzeichnisbaum** und benötigt keine LMO-Dateien.

---

## PHP 8.x / 8.5 Kompatibilitätshinweise

Das Script wurde ursprünglich für PHP 5 geschrieben. Folgende Punkte sind bei neueren PHP-Versionen zu beachten:

### 🔴 Kritisch

**`md5(uniqid(rand()))` — `rand()` ohne Argumente ist deprecated (PHP 8.3+)**

```php
// Alt:
UID:' . md5(uniqid(rand())) . '

// Besser:
UID:' . md5(uniqid('', true)) . '
```
`uniqid(rand())` übergibt eine Zufallszahl als Prefix — das funktioniert, aber `rand()` ohne Argumente in Kontexten wo ein String erwartet wird löst in PHP 8.3+ eine Deprecation-Notice aus. `uniqid('', true)` mit dem `more_entropy`-Parameter ist die sauberere Alternative.

**`fopen("spielplan.ics", "w+")` — relativer Pfad**

Das Script schreibt `spielplan.ics` in das aktuelle Arbeitsverzeichnis. Je nach PHP-Konfiguration (`open_basedir`, FastCGI-Einstellungen) kann das in PHP 8.x zu einem Fehler führen, wenn das Arbeitsverzeichnis nicht das Scriptverzeichnis ist. Robuster:

```php
$datei = fopen(__DIR__ . "/spielplan.ics", "w+");
```

### 🟡 Kleinere Punkte

**Zeitzonenbehandlung**

Das Script berechnet die UTC-Zeit durch pauschales Subtrahieren von 2 Stunden (`$zeit[0] - 2`), was für MEZ (UTC+1) falsch und für MESZ (UTC+2) zufällig richtig ist. Für korrekte Kalendertermine sollte die PHP-Zeitzonenfunktion verwendet werden:

```php
date_default_timezone_set('Europe/Berlin');
$dtstart = gmdate("Ymd\THis\Z", mktime($zeit[0], $zeit[1], 0, $datum[2], $datum[1], $jahr));
```

**`error_reporting(0)` — Fehler werden komplett unterdrückt**

Das Script unterdrückt alle Fehler. In einer Produktionsumgebung empfiehlt sich zumindest Logging:

```php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

**Keine Eingabevalidierung**

`$_POST['kopierterspielplan']` wird ungefiltert verwendet. Für einen öffentlich zugänglichen Server sollte eine Längenbeschränkung und Sanitisierung ergänzt werden.

---

## Versionsverlauf

| Version | Datum | Änderungen |
|---------|-------|------------|
| 1.1 | 24.07.2014 | Skriptoptimierung; Vereinsnamen können gekürzt werden |
| 1.0 | 23.07.2014 | Grundfunktionen |

---

## Lizenz

Siehe `LICENSE`-Datei im Repository.
