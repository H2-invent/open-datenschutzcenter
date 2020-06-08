# Open-Datenschutzcenter
__Open Source Datenschutzmanagement System__

Open Datenschutzcenter (ODC) ist eine FOSS (Free and Open Source) Enterprise Webanwendung, die auf einem eigenen Root oder vServer betrieben werden kann.

Auf Grund der Vendor Abhängigkeiten wird ein Webspace für den Betrieb der Webanwendung nicht empfohlen. Enterprise bedeutet, dass ODC als Manadatenlösung und interne Webanwendung betrieben werden kann. Die Verwaltung, Updates und Wartung muss durch die Zuständige und Verantwortliche Person durchgeführt werden.

## Mindestanforderungen an den Server:
* 2 GB RAM
* 1 v-CDU
* 30 GB SSH Festplatte
* SSH Zugriff
* SSL Zertifikat
* Apache Server
* MySQL Server

# Installation
Installationsanleitung und ein Foliensatz mit Screenshots steht auf https://h2-invent.com/software/odc zur Verfügung.

# Funktionen
Folgende Funktionen sind bereits im Open Datenschutzcenter integriert:
* Datenschutzakademie für interne Datenschutzschulungen zum Nachweis der kontinuierlichen Datenschutzweitergildung der Beschäftigten.
* Internes Datenschutzaudit mit einem umfangreichen Fragenkatalog, Begründungen und Hinweisen für die Umsetzung
* Abteilung einer globalen TOM aus den Audits.
* Erstellen von Technische und organisatorische Maßnahmen für Verarbeitungen.
* Erfassung von Verarbeitungen und Erstellung eines Verarbeitungsverzeichnisses mit Revisionen.
* Sortieren der Verarbeitungen nach Abteilungen und Produkten.
* Erfassung von Kontakten zu Auftragnehmern und Auftraggebern.
* Erfassung von Datenweitergaben und Auftragsverarbeitungen.
* Verlinkung von Datenweitergaben, Auftragsverarbeitungen mit Verarbeitungen und technischen und organisatorischen Maßnahmen.
* Erfassung von Datenschutzvorfällen mit Dokumentation von Folgen, Maßnahmen und Auswirkungen.
* Erstellung von Berichten als PDF und Excel.
* Stammdaten mit Abteilungen, Schutzzielen, Produkten und Nutzerverwaltung.
* Import von Auditfragen und Verarbeitungstätigkeiten als .odif Dateien.

### Import .odif Datei
Folgende Parameter stehen in den odif Dateien zum Import zur Verfügung und müssen auch im Dokument vorhanden sein.

__Import Verzeichnisse von Verarbeitungstätigkeiten:__

Es ist wichtig, dass die Import Dateien vor dem Import korrekt signiert wurden. Ohne Signatur funktioniert der Import nicht.
```
{
  "table": "VVT",
  "signature": "empty",
  "author": "Andreas Holzmann - H2 invent",
  "entry": [
    {
      "nummer": null
      "name": "Name der Verarbeitung",
      "grundlage": [
        "§ 99 BetrVG",
        "Art. 88 Abs. 1",
        "Gesetzliche Verpflichtung: Art. 6 Abs. c) EU-DSGVO"
      ],
      "zweck": "Das ist der Zweck der Verarbeitung",
      "personen": [
        "Besucher/Gäste",
        "Abonnenten"
      ],
      "datenCategorie": [
        "Planungsdaten",
        "Videoaufzeichnung"
      ],
      "status": "Erster Import der VVT",
      "risiko": [
        "Irritation",
        "Identitätsdiebstahl"
      ],
      "beurteilungEintritt": "1",
      "beurteilungSchaden": "1",
      "informationspflicht": "Informationen zu diesem VVT",
      "speicherung": "",
      "loeschfrist": "6 Monate",
      "weitergabe": "",
      "hinweisTom": "",
      "dsbKommentar": "",
      "dsfa": "true",
      "dsfaData": {
        "beschreibung": "Beschreibung der DSFA",
        "notwendigkeit": "Notwendigkeit der Verarbeitung",
        "risiko": "Risiko bei der Verarbeitung",
        "abhilfe": "Abhilfe zu Minderung der Risiken",
        "standpunkt": "Standpunkt des BRs",
        "dsbKommentar": "DSB Kommentar",
        "ergebnis": "Allgemeines Ergebnis der DSFA"
      }
    }
  ]
}
```


__Import Verzeichnisse von Auditfragen:__

Es ist wichtig, dass die Import Dateien vor dem Import korrekt signiert wurden. Ohne Signatur funktioniert der Import nicht.
```
{
  "table": Audit
  "signature": "empty",
  "author": "Andreas Holzmann - H2 invent",
  "entry": [
    {
      "nummer": null
      "frage": "Haben Sie einen DSB benannt?",
      "bemerkung": "Bemerkung zur Antwort und zum Status",
      "empfehlung": "Unsere Empfehlung zur Umsetzung der Aufitgrage",
      "ziel": [
        "integrität",
        "vertraulichkeit"
      ],
      "categorie": "Integrität",
      "status": "unbearbeitet"
    },
    {
      "frage": "wie heißt du?",
      "bemerkung": "bemerkung",
      "empfehlung": "meine empfehlung",
      "ziel": [
        "verfügbarkeit",
        "vertraulichkeit"
      ],
      "categorie": "Verfügbarkeit",
      "status": "unbearbeitet"
    },
    {
      "frage": "wie heißt du?",
      "bemerkung": "bemerkung",
      "empfehlung": "meine empfehlung",
      "ziel": [
        "verfügbarkeit",
        "vertraulichkeit"
      ],
      "categorie": "Verfügbarkeit",
      "status": "unbearbeitet"
    },
    {
      "frage": "wie heißt du?",
      "bemerkung": "bemerkung",
      "empfehlung": "meine empfehlung",
      "ziel": [
        "integrität",
        "vertraulichkeit"
      ],
      "categorie": "Integrität",
      "status": "unbearbeitet"
    }
  ]
}
```

# Lizenz
Die aktuelle Version von Open Datenschutzcenter wird unter der GPL-3.0 License bereitgestellt.
Copyright (c) 2020 anholzmann
