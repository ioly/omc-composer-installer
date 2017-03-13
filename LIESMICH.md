# OXID Modul Connector - Composer Installer

## Installation

Der Composer-Installer kann über die composer.json eures Projektes installiert werden. Diese
sollte aktuell im Hauptverzeichnis des Shops liegen, wo auch der "vendor"-Ordner erstellt wird.

__Hinweis__: OXID 6 wird durch die Struktur mit einem "source"-Unterordner noch nicht unterstützt, das kommt aber in Kürze! :)

In der "composer.json" könnt ihr dann direkt omc-Module über Composer installieren lassen, hier 
ein Beispiel:

```json
{
  "name": "my/oxid-project",
  "license": "MIT",
  "repositories": [
    {
      "name": "omc/omc-composer-installer",
      "type": "vcs",
      "url": "https://github.com/ioly/composer-installer.git"
    }
  ],
  "require": {
    "omc/omc-composer-installer": "*"
  },
  "extra": {
    "omc-composer-installer": {
      "oxidversion": "4.10",
      "cookbooks": {
        "omc": "https://github.com/OXIDprojects/OXID-Modul-Connector/archive/recipes.zip"
      },
      "modules": {
        "jkrug/ocbcleartmp": "1.0.0-v47",
        "acirtautas/oxidmoduleinternals": "0.3.1"
      },
      "settings": {
        "alwaysRunOnUpdate": "true"
      }
    }
  }
}
```

siehe auch "example-composer.json" im Projekt.
Im __"extra"__-Bereich konfiguriert ihr den omc Composer Installer, Pflicht sind hier mindestens "oxidversion", ein __"Cookbook"__ im "cookbooks"-Array
sowie natürlich mindestens ein omc-Modul ;)
Bei jedem "composer install" oder "composer update" wird dann das "modules"-Array durchgegangen und
noch nicht im Shop installierte Module automatisch (über den ioly-Core) installiert.

Aktuell wird nur die Installation der omc-Module über Composer unterstützt, deinstallieren muss man
aktuell noch "manuell" über die omc-Shell - die Module werden _nicht_ automatisch deinstalliert, wenn man die Einträge aus der "composer.json" entfernt.

## Disclaimer

Bitte beachtet, dass es sich um ein _Community-Projekt_ handelt, für das es keinerlei Garantie auf Komplettheit oder Richtigkeit der Inhalte gibt. Wenn Ihr das Projekt gut findet, freuen wir uns über das Einreichen weiterer Module oder auch gern über Euren Beitrag zur Weiterentwicklung.
Der Composer-Installer ist noch ein __Prototyp__, Verbesserungen und Vorschläge sind jederzeit willkommen!

> ACHTUNG! <br>
> Dieses Modul wurde für Entwicklungs- und Testzwecke gebaut.<br>Bitte installiert keine Module in Eurer Live-Umgebung!<br>Bitte sichert Eure Installation (Datenbank und Dateien), before Ihr Module über den OXID Modul Connector installiert!

## Lizenz
Der OXID Modul Connector ist unter MIT lizenziert.
Mehr Details findet Ihr in der [Lizenz-Datei](https://github.com/OXIDprojects/OXID-Module-Connector/blob/recipes/LICENSE).
