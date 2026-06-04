# spielplan2ics

**Addon für Liga Manager Online (LMO)**  
Erstellt von Marcus | Version 1.1 (24.07.2014) | Lizenz: GPL v2

---

## Was macht dieses Addon?

`spielplan2ics.php` wandelt einen kopierten LMO-Spielplan in eine ICS-Kalenderdatei (`spielplan.ics`) um, die in gängige Kalenderanwendungen wie Google Calendar, Apple Calendar oder Outlook importiert werden kann.

Das Script steht **vollständig unabhängig vom LMO-Verzeichnisbaum** und benötigt keine LMO-Dateien.

---

## Voraussetzungen

- PHP 8.0 oder höher (empfohlen)
- Webserver mit PHP-Unterstützung
- Schreibrechte im Verzeichnis des Scripts (für die Ausgabedatei `spielplan.ics`)

---

## Installation

1. `spielplan2ics.php` in ein beliebiges, über den Webserver erreichbares Verzeichnis kopieren
2. Sicherstellen dass das Verzeichnis **Schreibrechte** hat (die Ausgabedatei `spielplan.ics` wird dort erstellt)
3. Script direkt im Browser aufrufen, z.B.:
   ```
   https://example.com/tools/spielplan2ics.php
   ```

---

## Bedienung

### Schritt 1 — Im LMO
- Die gewünschte Liga im LMO öffnen
- Unter **Spielpläne** die gewünschte Mannschaft auswählen
- Die Maus **vor dem 1. Spieltag** positionieren
- Den gesamten Spielplan mit der Maus markieren und in die Zwischenablage kopieren

### Schritt 2 — In spielplan2ics.php
- Das Script im Browser aufrufen
- Den kopierten Text in das Textfeld einfügen
- Auf **„ICS-Datei erstellen"** klicken
- Die erzeugte Datei `spielplan.ics` liegt danach im selben Verzeichnis wie das Script

### Schritt 3 — Kalender importieren
- Die erzeugte `spielplan.ics` in den gewünschten Kalender importieren (Google Calendar, Apple Calendar, Outlook usw.)

---

## Vereinsnamen kürzen

Im Script können Vereinsnamen durch Kurzformen ersetzt werden. Die Konfiguration befindet sich in den Arrays `$suchen` und `$durchdasersetzen`:

```php
$suchen          = array("    -    ", "BC Erlbach 1919");
$durchdasersetzen = array(" - ",      "BCE");
```

Weitere Einträge können nach demselben Muster ergänzt werden. Der erste Eintrag (`"    -    "` → `" - "`) normalisiert die Trennzeichen zwischen Heim- und Gastmannschaft und sollte immer vorhanden bleiben.

---

## Technische Details

- Die Ausgabedatei wird mit `fopen(__DIR__ . "/spielplan.ics", "w+")` im Verzeichnis des Scripts erzeugt
- Zeitzonen werden korrekt über `date_default_timezone_set('Europe/Berlin')` und `mktime()`/`gmdate()` verarbeitet — Sommer- und Winterzeit werden automatisch berücksichtigt
- Jeder Kalendereintrag erhält eine eindeutige UID via `md5(uniqid('', true))`
- Spieltermine werden mit einer Dauer von **2 Stunden** eingetragen
- Die Jahresbestimmung erfolgt automatisch: Wenn die Monatsnummer sinkt (Jahreswechsel im Spielplan), wird das Folgejahr verwendet
- Fehlerausgaben sind im Code deaktiviert (`error_reporting` auskommentiert) — für Debugging-Zwecke die entsprechende Zeile im Script aktivieren

---

## Versionsverlauf

| Version | Datum | Änderungen |
|---------|-------|------------|
| 1.2 | 2025 | PHP 8.x Kompatibilität: korrekte Zeitzonenbehandlung (`mktime`/`gmdate`), `__DIR__` für Dateipfad, `uniqid('', true)` statt `uniqid(rand())`, `error_reporting` auskommentiert |
| 1.1 | 24.07.2014 | Skriptoptimierung; Vereinsnamen können gekürzt werden |
| 1.0 | 23.07.2014 | Grundfunktionen |

---

## Lizenz

GNU General Public License v2 — siehe `LICENSE`-Datei im Repository.
