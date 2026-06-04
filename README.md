# spielplan2ics

**Addon für Liga Manager Online (LMO)**  
| Autor | Version | Lizenz |
|-------|---------|--------|
| Marcus | Version bis 1.1 | GPL v2 |  
| Henshingly | ab Version 1.2 | GPL v2 |  

---

## Was macht dieses Addon?

`spielplan2ics.php` wandelt einen kopierten LMO-Spielplan in eine ICS-Kalenderdatei um, die in gängige Kalenderanwendungen wie Google Calendar, Apple Calendar oder Outlook importiert werden kann.

Das Script steht **vollständig unabhängig vom LMO-Verzeichnisbaum** und benötigt keine LMO-Dateien.

---

## Voraussetzungen

- PHP 8.0 oder höher
- Webserver mit PHP-Unterstützung
- Schreibrechte im Verzeichnis des Scripts (für die Ausgabedatei)

---

## Installation

1. `spielplan2ics.php` in ein beliebiges, über den Webserver erreichbares Verzeichnis kopieren
2. Sicherstellen dass das Verzeichnis **Schreibrechte** hat
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
- **Sprache des LMO** auswählen — die Seite wechselt sofort in die gewählte Sprache
- **Zeitzone** auswählen (Standard: Europe/Berlin)
- Den kopierten Text in das Textfeld einfügen
- Optional: einen eigenen **Dateinamen** für die ICS-Datei eingeben (Standard: `spielplan`)
- Auf **„ICS-Datei erstellen"** klicken
- Nach der Verarbeitung erscheint ein **direkter Downloadlink** zur erzeugten ICS-Datei

### Schritt 3 — Kalender importieren
- Über den Downloadlink die ICS-Datei speichern
- Die Datei in den gewünschten Kalender importieren (Google Calendar, Apple Calendar, Outlook usw.)

---

## Sprachauswahl

Das Formular passt sich automatisch der gewählten LMO-Sprache an. Alle Beschriftungen, Schaltflächen und Ausgabetexte erscheinen in der gewählten Sprache. Die Auswahl wirkt sich auch auf folgendes aus:

- **Wochentag-Erkennung** im Datumsfeld (z.B. `Lunes` für Spanisch, `Ponedjeljak` für Kroatisch)
- **ICS-Metadaten**: `PRODID`, `CATEGORIES;LANGUAGE`, `DESCRIPTION;LANGUAGE`, `SUMMARY;LANGUAGE`

Unterstützte Sprachen: Deutsch, Englisch, Französisch, Italienisch, Spanisch, Portugiesisch, Niederländisch, Tschechisch, Ungarisch, Kroatisch, Bosnisch, Slowenisch, Rumänisch, Norwegisch.

---

## Zeitzonenauswahl

Die Zeitzone bestimmt die korrekte UTC-Umrechnung der Spielzeiten in der ICS-Datei. Standard ist `Europe/Berlin`. Alle europäischen Zeitzonen der 14 LMO-Sprachen stehen zur Auswahl.

---

## Dateiname der ICS-Datei

Im Formular kann vor dem Erstellen ein eigener Dateiname eingegeben werden:

```
Dateiname der ICS-Datei: [ fcbayern          ] .ics
```

- Der Standardwert ist `spielplan`
- Sonderzeichen wie `\ / : * ? < > | "` werden automatisch durch `-` ersetzt
- Die Endung `.ics` wird automatisch ergänzt und muss nicht eingegeben werden

---

## Vereinsnamen kürzen

Im Script können Vereinsnamen durch Kurzformen ersetzt werden. Die Konfiguration befindet sich in den Arrays `$suchen` und `$durchdasersetzen`:

```php
$suchen          = array("BC Erlbach 1919");
$durchdasersetzen = array("BCE");
```

Weitere Einträge können nach demselben Muster ergänzt werden.

---

## Unterstützte Datumsformate

Der LMO-Administrator kann das Datumsformat frei nach `DateTimeInterface::format` konfigurieren. Das Script erkennt automatisch alle gängigen Kombinationen:

| PHP-Format | Beispiel | Bemerkung |
|---|---|---|
| `d.m.Y H:i` | `22.08.2025 20:30` | Vollständig mit Jahr und Uhrzeit |
| `d.m.y H:i` | `22.08.25 20:30` | 2-stelliges Jahr |
| `d.m. H:i` | `22.08. 20:30` | Ohne Jahr — wird aus Monatsverlauf abgeleitet |
| `j.n.Y H:i` | `22.8.2025 20:30` | Ohne führende Nullen |
| `j.n.` | `22.8.` | Kurz, ohne Jahr |
| `Y-m-d H:i` | `2025-08-22 20:30` | ISO-Format |
| `D d.m.Y H:i` | `Fr 22.08.2025 20:30` | Mit Wochentag-Prefix (sprachspezifisch erkannt) |
| `l d.m.Y H:i` | `Freitag 22.08.2025 20:30` | Mit langem Wochentag-Prefix |

Fehlt die Uhrzeit, wird `00:00` angenommen. Fehlt das Jahr, wird es aus dem Monatsverlauf der Saison abgeleitet — auch über den Jahreswechsel hinweg (z.B. August bis Mai).

---

## Technische Details

- Die Ausgabedatei wird im Verzeichnis des Scripts gespeichert (`__DIR__`)
- Der Dateiname wird serverseitig gegen unerlaubte Zeichen bereinigt
- Zeitzonen werden korrekt über `date_default_timezone_set()` und `mktime()`/`gmdate()` verarbeitet — Sommer- und Winterzeit werden automatisch berücksichtigt
- Jeder Kalendereintrag erhält eine eindeutige UID im Format `hash@spielplan2ics`
- Spieltermine werden mit einer Dauer von **2 Stunden** eingetragen
- Sonderzeichen (Kommas, Semikolons) in Teamnamen werden gemäß RFC 5545 korrekt escaped
- Fehlerausgaben sind deaktiviert — für Debugging die entsprechenden Zeilen im Script aktivieren

---

## ICS-Format (RFC 5545 konform)

Die erzeugte Datei entspricht vollständig dem iCalendar-Standard RFC 5545 und wird von allen gängigen Kalenderanwendungen korrekt importiert:

| Eigenschaft | Wert |
|-------------|------|
| `VERSION` | 2.0 |
| `CALSCALE` | GREGORIAN |
| `METHOD` | PUBLISH |
| `PRODID` | `-//Liga Manager Online//spielplan2ics//[SPRACHE]` |
| Zeilenenden | CRLF (wie RFC vorgeschrieben) |
| Zeitzone | UTC (korrekte Umrechnung aus gewählter Zeitzone) |
| Zeichensatz | UTF-8 |
| Sonderzeichen | escaped (`\,` `\;`) |

---

## Versionsverlauf

| Version | Datum | Änderungen |
|---------|-------|------------|
| 1.5 | 04.06.2026 | Sprachauswahl für alle 14 LMO-Sprachen mit sofortiger Seitenübersetzung beim Wechsel; Zeitzonenauswahl im Formular; PRODID, LANGUAGE-Tags dynamisch je Sprache; vollständige UI-Übersetzung |
| 1.4 | 03.06.2026 | Zwischenversion (in 1.5 aufgegangen) |
| 1.3 | 02.06.2026 | Eingabefeld für benutzerdefinierten ICS-Dateinamen; Direktlink zum Download; automatische Erkennung aller `DateTimeInterface::format`-Datumsformate inkl. Wochentag-Prefix |
| 1.2 | 01.06.2026 | PHP 8.x Kompatibilität: korrekte Zeitzonenbehandlung, `__DIR__`, `uniqid('', true)`, `error_reporting` auskommentiert; RFC 5545: `METHOD:PUBLISH`, `CALSCALE:GREGORIAN`, `DTSTAMP` mit Z-Suffix, `ENCODING=QUOTED-PRINTABLE` entfernt, Sonderzeichen-Escaping, eigene PRODID, UID mit Domain-Suffix |
| 1.1 | 24.07.2014 | Skriptoptimierung; Vereinsnamen können gekürzt werden |
| 1.0 | 23.07.2014 | Grundfunktionen |

---

## Lizenz

GNU General Public License v2 — siehe `LICENSE`-Datei im Repository.
