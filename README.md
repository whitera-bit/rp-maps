# Roleplay Maps

Es können im ACP eigene Umgebungskarten angelegt werden, denen Orte in der Karte selbst mit optionaler Beschreibung hinzugefügt werden können. Dabei besteht die Möglichkeit, dass auch Mitglieder selbst Orte vorschlagen. Dabei können Bewohner:innen eingetragen werden - stimmt ein Name mit einem Username überein, wird der User in der Karte mit seinem Profil verlinkt.

Statt die Koordinaten manuell einzutragen, lässt sich auf der Karte mittels Strg + M ein Modus aufrufen/verlassen, der nun bei Klick auf einen Punkt die Koordinaten speichert und das Formular öffnet.

# Installation

## Voraussetzungen

* [MyBB 1.8.*](https://www.mybb.de/downloads/)
* PHP ≥ 7.0
* [PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary)
___

Lade den Inhalt des Ordners `/Upload` den darin angegebenen Ordnern entsprechend hoch.

Installiere und aktiviere das Plugin im ACP unter *Konfiguration > Plugins*.

# Konfiguration

Unter *Konfiguration > Eigene Karten* können eigene Umgebungskarten angelegt und verwaltet werden.

## Variablen

Im Profil (member_profile) kann an beliebiger Stelle `{$map_location}` eingefügt werden, um den oder die Wohnorte einer Person auszugeben.

## Icons

Die Icons stammen von [Vectors Market](https://www.flaticon.com/authors/vectors-market) auf [www.flaticon.com](https://www.flaticon.com/). Bitte füge Credits hinzu oder tausche die Icons aus. Alle Bilder, die in `images/map` enthalten sind, stehen automatisch im Formular zur Auswahl.
