# spielplan2ics

**Addon für Liga Manager Online (LMO)**

| Autor      | Version          | Lizenz |
|------------|------------------|--------|
| Marcus     | Version bis 1.1  | GPL v2 |
| Henshingly | ab Version 1.2   | GPL v2 |

---

## Was macht dieses Addon?

`spielplan2ics.php` wandelt einen aus dem LMO kopierten Spielplan (Punktspiele oder Turnier-/KO-Format) in eine
ICS-Kalenderdatei um, die in gängige Kalenderanwendungen wie Google Calendar, Apple Calendar, Mozilla Thunderbird
oder Outlook importiert werden kann.

Das Script steht **vollständig unabhängig vom LMO-Verzeichnisbaum** und benötigt keine LMO-Dateien.
Eine bebilderte Schritt-für-Schritt-Anleitung liegt als PDF bei
(`Anleitung zum Erstellen von ICS-Kalenderdateien mittels spielplan2ics.pdf`).

---

## Voraussetzungen

- PHP 8.0 oder höher (Erweiterung `mbstring`)
- Webserver mit PHP-Unterstützung
- Schreibrechte im Verzeichnis des Scripts (für die Ausgabedatei)

---

## Installation

1. `spielplan2ics.php` in ein beliebiges, über den Webserver erreichbares Verzeichnis kopieren
2. Sicherstellen, dass das Verzeichnis **Schreibrechte** hat
3. Script direkt im Browser aufrufen, z. B.:
   ```
   https://example.com/tools/spielplan2ics.php
   ```

> **Hinweis zur Sicherheit:** Die erzeugten `.ics`-Dateien werden im Verzeichnis des Scripts gespeichert und sind
> dort öffentlich abrufbar. Das Script selbst kann von jedem aufgerufen werden, der die Adresse kennt.
> Wenn es nicht öffentlich nutzbar sein soll, das Verzeichnis per Passwortschutz (`.htaccess`/`.htpasswd`)
> sichern und erzeugte Dateien regelmäßig löschen.

---

## Bedienung

### Schritt 1 – Im LMO
- Die gewünschte Liga im LMO öffnen
- Unter **Spielpläne** die gewünschte Mannschaft auswählen
- Die Maus **vor dem 1. Spieltag** positionieren
- Den gesamten Spielplan mit der Maus markieren und in die Zwischenablage kopieren

### Schritt 2 – In spielplan2ics.php
- Das Script im Browser aufrufen
- Den kopierten Text in das Textfeld einfügen
- **Kalendersprache** auswählen – die Seite wechselt sofort in die gewählte Sprache
- **Zeitzone** auswählen (Standard: Europe/Berlin)
- **URL** angeben, die bei jedem Termin im Kalender hinterlegt wird (Vorgabe: Adresse des eigenen Servers;
  erlaubt sind nur `http://` und `https://`)
- Optional: einen eigenen **Dateinamen** für die ICS-Datei eingeben (Standard: `spielplan`)
- Auf **„ICS-Datei erstellen"** klicken
- Nach der Verarbeitung erscheint eine Liste der erzeugten Termine, ein **Downloadlink** zur ICS-Datei und ein Button,
  der den vollständigen Link in die Zwischenablage kopiert (z. B. zum Abonnieren im Kalender)

### Schritt 3 – Kalender importieren
- Über den Downloadlink die ICS-Datei speichern
- Die Datei in den gewünschten Kalender importieren (Google Calendar, Apple Calendar, Outlook usw.)

---

## Erkannte Formate

Das Script erkennt den Aufbau des kopierten Textes automatisch:

| Format                 | Aufbau (durch Tabulator getrennte Spalten)                   | Kategorie im Kalender |
|------------------------|--------------------------------------------------------------|-----------------------|
| LMO-Spielplan          | Spieltag · Datum · Heim · „-“ · Gast …                       | Punktspiel            |
| Turnier-/KO-Format     | Datum+Uhrzeit · Heim · Gast (fortlaufende Nummer als Spiel)  | Turnierspiel          |

Zeilen, die nicht passen (Überschriften, Leerzeilen), werden übersprungen.

---

## Sprachauswahl

Das Formular passt sich der gewählten Kalendersprache an. Alle Beschriftungen, Schaltflächen und Ausgabetexte
erscheinen in dieser Sprache. Die Auswahl wirkt sich auch auf folgendes aus:

- **Wochentag-Erkennung** im Datumsfeld (z. B. `Lunes` für Spanisch, `Ponedjeljak` für Kroatisch)
- **ICS-Metadaten**: `PRODID`, `CATEGORIES;LANGUAGE`, `DESCRIPTION;LANGUAGE`, `SUMMARY;LANGUAGE`
- Die Wörter „Spiel“ und „Spieltag“ in der Beschreibung der Termine

Unterstützte Sprachen: Deutsch, Englisch, Französisch, Italienisch, Spanisch, Portugiesisch, Niederländisch,
Tschechisch, Ungarisch, Kroatisch, Bosnisch, Slowenisch, Rumänisch, Norwegisch.

---

## Zeitzonenauswahl

Die Zeitzone bestimmt die korrekte UTC-Umrechnung der Spielzeiten in der ICS-Datei. Standard ist `Europe/Berlin`.
Sommer- und Winterzeit werden automatisch berücksichtigt.

---

## Dateiname der ICS-Datei

Im Formular kann vor dem Erstellen ein eigener Dateiname eingegeben werden:

```
Dateiname der ICS-Datei: [ fcmusterstadt-herren ] .ics
```

- Der Standardwert ist `spielplan`
- Sonderzeichen wie `\ / : * ? < > | "` und Leerzeichen werden automatisch durch `-` ersetzt
- Führende Punkte werden entfernt, der Name wird auf 80 Zeichen begrenzt
- Die Endung `.ics` wird automatisch ergänzt und muss nicht eingegeben werden
- Eine vorhandene Datei mit gleichem Namen wird überschrieben

---

## Vereinsnamen kürzen

Im Script können Vereinsnamen durch Kurzformen ersetzt werden. Die Konfiguration befindet sich in den Arrays
`$suchen` und `$durchdasersetzen`:

```php
$suchen           = ['BC Erlbach 1919'];
$durchdasersetzen = ['BCE'];
```

Weitere Einträge können nach demselben Muster ergänzt werden (beide Arrays müssen gleich viele Einträge haben).

---

## Unterstützte Datumsformate

Der LMO-Administrator kann das Datumsformat frei nach `DateTimeInterface::format` konfigurieren.
Das Script erkennt automatisch alle gängigen Kombinationen:

| PHP-Format   | Beispiel                   | Bemerkung                                          |
|--------------|----------------------------|----------------------------------------------------|
| `d.m.Y H:i`  | `22.08.2025 20:30`         | Vollständig mit Jahr und Uhrzeit                   |
| `d.m.y H:i`  | `22.08.25 20:30`           | 2-stelliges Jahr                                   |
| `d.m. H:i`   | `22.08. 20:30`             | Ohne Jahr – wird aus Monatsverlauf abgeleitet      |
| `j.n.Y H:i`  | `22.8.2025 20:30`          | Ohne führende Nullen                               |
| `j.n.`       | `22.8.`                    | Kurz, ohne Jahr                                    |
| `Y-m-d H:i`  | `2025-08-22 20:30`         | ISO-Format                                         |
| `D d.m.Y H:i`| `Fr 22.08.2025 20:30`      | Mit Wochentag-Präfix (sprachspezifisch erkannt)    |
| `l d.m.Y H:i`| `Freitag 22.08.2025 20:30` | Mit langem Wochentag-Präfix                        |

Fehlt die Uhrzeit, wird `00:00` angenommen. Fehlt das Jahr, wird es aus dem Monatsverlauf der Saison abgeleitet –
auch über den Jahreswechsel hinweg (z. B. August bis Mai).

---

## Technische Details

- Die Ausgabedatei wird im Verzeichnis des Scripts gespeichert (`__DIR__`); ist es nicht beschreibbar, erscheint eine Fehlermeldung
- Spieltermine werden mit einer Dauer von **2 Stunden** eingetragen
- Jeder Kalendereintrag erhält eine eindeutige UID im Format `hash@spielplan2ics`
- Sonderzeichen (Kommas, Semikolons, Backslashes) in Teamnamen werden gemäß RFC 5545 escaped
- Ausgaben auf der Webseite (Terminliste, Dateiname, URL) werden HTML-sicher ausgegeben
- Die eingegebene URL darf keine Leer- oder Steuerzeichen enthalten (sonst wird die Server-Adresse verwendet);
  damit lassen sich keine zusätzlichen Zeilen in die ICS-Datei einschleusen
- Fehlerausgaben sind nicht unterdrückt – für den Produktivbetrieb in der PHP-Konfiguration `display_errors` ausschalten

---

## ICS-Format (RFC 5545 konform)

| Eigenschaft   | Wert                                                       |
|---------------|------------------------------------------------------------|
| `VERSION`     | 2.0                                                        |
| `CALSCALE`    | GREGORIAN                                                  |
| `METHOD`      | PUBLISH                                                    |
| `PRODID`      | `-//Liga Manager Online//spielplan2ics//[SPRACHE]`         |
| Zeilenenden   | CRLF (wie vom RFC vorgeschrieben, wird vom Script erzwungen) |
| Zeilenlänge   | max. 75 Oktette, längere Zeilen werden gefaltet            |
| Zeitzone      | UTC (korrekte Umrechnung aus der gewählten Zeitzone)       |
| Zeichensatz   | UTF-8                                                      |
| Sonderzeichen | escaped (`\,` `\;` `\\`)                                   |

---

## Versionsverlauf

| Version | Datum      | Änderungen |
|---------|------------|------------|
| 1.8     | 25.11.2026 | Sicherheit: HTML-sichere Ausgabe, URL ohne Leer-/Steuerzeichen, Host-Header geprüft, JavaScript des Kopier-Buttons per `json_encode`, Dateiname begrenzt; ICS: CRLF erzwungen und Zeilenfaltung; Fehlerbehandlung für fehlende Felder und nicht beschreibbares Verzeichnis; Merge-Konflikt-Markierungen entfernt; PSR-12-Formatierung |
| 1.7     | 05.06.2026 | „Sprache des LMO“ heißt jetzt „Kalendersprache“; URL-Eingabefeld (wird im ICS-Termin hinterlegt); „Spiel“/„Spieltag“ in der Beschreibung mehrsprachig |
| 1.6     | 05.06.2026 | Automatische Formaterkennung: LMO-Spielplan und Turnier-/KO-Format |
| 1.5     | 04.06.2026 | Sprachauswahl für alle 14 LMO-Sprachen mit sofortiger Übersetzung; Zeitzonenauswahl; PRODID und LANGUAGE-Tags dynamisch; vollständige UI-Übersetzung |
| 1.4     | 03.06.2026 | Zwischenversion (in 1.5 aufgegangen) |
| 1.3     | 02.06.2026 | Eingabefeld für ICS-Dateinamen; Direktlink zum Download; Erkennung aller `DateTimeInterface::format`-Datumsformate inkl. Wochentag-Präfix |
| 1.2     | 01.06.2026 | PHP 8.x-Kompatibilität (Zeitzonen, `__DIR__`, `uniqid('', true)`); RFC 5545: `METHOD:PUBLISH`, `CALSCALE:GREGORIAN`, `DTSTAMP` mit Z-Suffix, Sonderzeichen-Escaping, eigene PRODID, UID mit Domain-Suffix |
| 1.1     | 24.07.2014 | Skriptoptimierung; Vereinsnamen können gekürzt werden |
| 1.0     | 23.07.2014 | Grundfunktionen |

---

## Lizenz

GNU General Public License v2 – siehe `LICENSE`-Datei im Repository.
