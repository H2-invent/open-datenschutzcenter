# Docker Compose Repo for Open Datenschutzcenter


## Installation of Open Datenschutzcenter
Use the root user to setup docker and the Open Datenschutzcenter
```sh
su root
```

This script will setup a repo file and install docker and docker-compose.
```sh
echo "Execute Docker Install Scipt"
wget https://git.h2-invent.com/Meetling/Docker-Compose/raw/branch/main/installDocker.sh && bash installDocker.sh
rm installDocker.sh
```

To use the setup script for docker-compose, run the following commands.
```sh
echo "Download Setup Script"
wget https://git.h2-invent.com/Datenschutzcenter/Docker-Compose/raw/branch/main/setup.sh
echo "Execute Setup Script"
bash setup.sh
echo "Remove Setup Script"
rm setup.sh
echo "Execute Docker Compose"
cd /opt/odc && docker-compose up -d
```


## Working directory
The setup script will clone and add files into the directory /opt/odc.

You can run the composer command inside this directory again to update the containers or check the variables which are used to setup the containers.

```sh
cd /opt/odc
```
Following Files are important and have been changed and setup with the script:
* .env.local
* docker.config
* docker-compose.yml


## Add additional custom configs

To add additional custom environment variables, you can add a file .env.custom and add your variables there.
This file will not be changed from the setup script and will be used inside all containers to overwrite the existing .env and .env.local.
All environment variables can be found inside the Git Repository: [Open Datenschutzcenter](https://github.com/H2-invent/open-datenschutzcenter/blob/master/.env)
```sh
MAILER_DSN='smtps://<username>:<password>@<smtpHost>:<smtpPort>'
registerEmailAdress=register@local.local
registerEmailName=Datenschutzcenter
defaultEmailAdress=test@test.com
defaultEmailName=test
AKADEMIE_EMAIL=akademie@lokal.lokal
DEFAULT_EMAIL=notification@lokal.lokal
DEV_EMAIL=dev@lokal.lokal
SUPPORT_MAIL=support@lokal.lokal

imprint=https://h2-invent.com/imprint
dataPrivacy=https://h2-invent.com/gdpr
superAdminRole=odc-super-admin

CRON_TOKEN=token
```
