# Open-Datenschutzcenter
__Open Source Datenschutzmanagement System__

[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](code_of_conduct.md)

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

# Get Started
Installationsanleitung und ein Foliensatz mit Screenshots steht auf https://h2-invent.com/software/odc zur Verfügung.

#### 1. Download oder Git Clone
Laden Sie sich die Dokumente als .ZIP Archiv auf Ihren lokalen PC oder über SSH mit Git Clone direkt auf den Server herunter.

Entpacken Sie das ZIP Archiv an dem gewünschten Ort und führen Sie die Installation über die Server (SSH) Konsole fort.

In diesem Archiv sind alle SRC und Template Files enthalten. Die Datenbank muss später separat angelegt und eingerichtet werden. Hierfür bieten wir eine vorgefertigte SQL Datei zum Kaufen an.

#### 2. Datenbank anlegen
Open Datenschutzcenter arbeitet aktuell nur mit einer MySQL oder MariaDB Datenbank.

Legen Sie eine leere SQL Datenbank an.

#### 3. .env.local anlegen
Erstellen Sie eine leere .env.local Datei im Root Verzeichnis von ODC und kopieren Sie den Text mit Ihren Anpassungen in das Dokument.

```
###> symfony/doctrine ###
DATABASE_URL=mysql://user:password@server_ip:3306/db_name
#DATABSE_SERVER=mariadb-10.4.6


###> symfony/framework-bundle ###
#APP_ENV=dev
APP_SECRET= "eigene App Secret id"


MAILER_URL=smtp://server_name:465?encryption=ssl&auth_mode=plain&username=username&password=password
MAILER_HOST=server_name
MAILER_PORT=587
MAILER_PASSWORD=password
MAILER_USERNAME=username
MAILER_ENCRYPTION=tls
MAILER_TRANSPORT=smtp


###ODC Parameters###
imprint="Link zu Imprint"
dataPrivacy="Link zu Datneschutzerklärung"

###< Emails###
registerEmailAdress=register@local.local
registerEmailName=Datenschutzcenter
defaultEmailAdress=test@test.com
defaultEmailName=test

AKADEMIE_EMAIL=akademie@lokal.lokal
DEV_EMAIL=dev@lokal.lokal
```

#### 4. Vendor Dateien mit dem Composer installieren
Im nächsten Schritt müssen Sie alle Vendor Dateien über den composer installieren

Führen Sie dabei den Befehl in der SSH Konsole aus.
```shell script
php composer.phar install
```

#### 5. Datenbank initiieren
Erstellen Sie das Datenbank Schema über die SSH Konsole mit einem der folgenden Befehle.

Führen Sie dabei den Befehl in der SSH Konsole aus.
```shell script
php bin/console doctrine:schema:create
php bin/console doctrine:schema:update --force
```

#### 6. Default User anlegen
Der Open-Datenschutzcenter ist __Mandaten fähig (Team)__. 
Dafür muss vor der ersten Nutzung ein Default Team und ein Default Benutzer angelegt werden. 
Dies ist aktuell nur in der Datenbank über z.B. phpmyAdmin oder Dotrine möglich.
* __Default Team id__: 1
* __Default User id__: 1

Nutzen Sie dafür HeidiSQL oder phpMyAdmin um in jeder Tabelle einen neuen Eintrag zu erstellen. 
Es handelt sich dabei um die Tabellen Fos_user und Team. 
Es muss erst das Team angelegt werden und dann der User.

#### 7. Default Daten anlegen
Nach dem das Default Team angelegt ist, müssen noch alle möglichen Status, Stand, Ziele und Abteilungen angelegt werden. Der Einfachheit halber wurde hier auch auf HeidiSQL gesetzt. Die Daten müssen daher direkt in die Datenbank eingetragen werden ohne User Interface. Die Standard (Default) Daten dürfen sich auch nicht änder und müssen daher nur einmal für alle Teams angelegt werden. 

Default Werte für die TOMs und die Auditfragen
* AuditTomZiele
* AuditTomStatus

Default Werte für die Datenweitergabe und Auftragsverarbeitung

* DatenweitergabeGrundlagen
* DatenweitergabeStand

Default Werte für das Verzeichnis für Verarbeitungstätigkeiten
* VVTGrundlagen
* VVTPersonen
* VVTRisiken
* VVTStatus

#### 8. Datenbank verschlüsseln
ODC nutzt einen Halite Key für die Verschlüsselung der Datenbank. Starten Sie die erste Verschlüsselung der Datenbank über die Konsole. Die Datenbank kann mit Hilfe des Halite Keys wieder entschlüsselt werden.

```shell script
Verschlüsseln der Datenbank
php bin/console doctrine:encrypt:database

Entschlüsseln der Datenbank
php bin/console doctrine:decrypt:database
```

Wichtig: Verschlüsseln Sie nur einmal die Datenbank und erstellen Sie gegelmäßig Backups der Datenbank und des Halite.Keys. Ohne diesen können Sie die Datenbank nicht mehr lesen.


#### 9. Server Hardening
Es liegt im Ermessen des Betreibers, den Server gegen Hackenangriffe zu schützen.

Nutzen Sie Server in einer private Cloud zu betreiben und die Angriffsvektoren so gering wie möglich zu halten.
Setzten Sie eine Server Firewall und einen nginx Load Ballancer für eine bessere Verfügbarkeit ein.
Installieren Sie auditd auf den Servern um mögliche Konfigurationsänderungen festhalten zu können.
Nutzen Sie Fail2Ban für einen kontrollierteren SSH Zugriff auf den Servern
Konfigurieren Sie ssh Key basierten SSH Zugriff

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
