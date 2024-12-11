#!/bin/bash

echo "Welcome to the Docker Installer:"
echo ""

echo ""
echo "1. The installer will update all Repos"
apt update 

echo ""
echo "2. The installer will install CURL and GIT"
apt install curl git -y

echo ""
echo "3. The installer will install Docker and Docker Compose"
apt install docker docker-compose -y

echo ""
echo "4. The installer will clone the Docker-Compose Repository"
HOME_DIR=/opt/odc
if [ -d $HOME_DIR ]
then
  cd $HOME_DIR
  git add . && git stash && git pull
else
  mkdir $HOME_DIR && cd $HOME_DIR
  git clone https://git.h2-invent.com/datenschutzcenter/docker-compose.git .
fi

echo ""
echo "5. The installer will setup all scripts, so you can docker-compose up"
echo ""
FILE=docker.config
if [ -f "$FILE" ]; then
    source $FILE
else
    touch $FILE
    KEYCLOAK_PW=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
    ODC_DB_PW=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
    KEYCLOAK_ADMIN_PW=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
    NEW_UUID=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)

    echo "KEYCLOAK_PW=$KEYCLOAK_PW" >> $FILE
    echo "KEYCLOAK_ADMIN_PW=$KEYCLOAK_ADMIN_PW" >> $FILE
    echo "NEW_UUID=$NEW_UUID" >> $FILE
    echo "ODC_DB_PW=$ODC_DB_PW" >> $FILE
    source $FILE
fi

  HTTP_METHOD=${HTTP_METHOD:=http}
  read -p "Enter http/https for testing on local environment ALWAYS use http [$HTTP_METHOD]: " input
  HTTP_METHOD=${input:=$HTTP_METHOD}
  sed -i '/HTTP_METHOD/d' $FILE
  echo "HTTP_METHOD=$HTTP_METHOD" >> $FILE

  PUBLIC_URL=${PUBLIC_URL:=dev.domain.de}
  read -p "Enter the url you want to enter the open-datenschutzcenter without http://, https:// or ports [$PUBLIC_URL]: " input
  PUBLIC_URL=${input:=$PUBLIC_URL}
  sed -i '/PUBLIC_URL/d' $FILE
  echo "PUBLIC_URL=$PUBLIC_URL" >> $FILE


HOST_IP=$(ip a | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1' | head -n 1)

echo ------------------------------------------------------------
echo --- 1. Build the Keycloak ----------------------------------
echo ------------------------------------------------------------
echo ""

cp .templates/realm-export.json keycloak/realm-export.json
sed -i "s|<clientsecret>|$NEW_UUID|g" keycloak/realm-export.json
sed -i "s|<clientUrl>|$HTTP_METHOD://$PUBLIC_URL|g" keycloak/realm-export.json

echo ------------------------------------------------------------
echo ------ 2. Build Mysql Init DB ------------------------------
echo ------------------------------------------------------------
echo ""

cp .templates/init-userdb.sql mysql-initdb/init-userdb.sql
sed -i "s|<open-datenschutzcenter-pw>|$ODC_DB_PW|g" mysql-initdb/init-userdb.sql
sed -i "s|<keycloak-pw>|$KEYCLOAK_PW|g" mysql-initdb/init-userdb.sql


echo ------------------------------------------------------------
echo --------- 3. Setup .ENV.LOCAL ------------------------------
echo ------------------------------------------------------------
echo ""

touch .env
touch .env.custom

if [ -f .env.local ]; then
    rm .env.local
fi

cat <<EOL > .env.local
# ENV FOR ODC
APP_SCHEME='$HTTP_METHOD'
PUBLIC_URL='$PUBLIC_URL'
MAILER_DSN='null://null'
DATABASE_URL='mysql://odc:$ODC_DB_PW@db-odc:3306/odc'
OAUTH_KEYCLOAK_SERVER='$HTTP_METHOD://$PUBLIC_URL/keycloak'
OAUTH_KEYCLOAK_CLIENT_ID=opendatenschutzcenter
OAUTH_KEYCLOAK_CLIENT_SECRET=$NEW_UUID
OAUTH_KEYCLOAK_REALM=opendatenschutzcenter
laF_version='3.0.4'
demo_installation='demo'
KEYCLOAK_ADMIN=admin
KEYCLOAK_ADMIN_PASSWORD=$KEYCLOAK_ADMIN_PW
KC_DB=mariadb
KC_DB_USERNAME=keycloak
KC_DB_PASSWORD=$KEYCLOAK_PW
KC_DB_URL='jdbc:mariadb://db-odc:3306/keycloak'
KC_HOSTNAME_URL='$HTTP_METHOD://$PUBLIC_URL/keycloak'
KC_HOSTNAME_PATH='$HTTP_METHOD://$PUBLIC_URL/keycloak'
KC_HOSTNAME_ADMIN_URL='$HTTP_METHOD://$PUBLIC_URL/keycloak'
KC_HTTP_RELATIVE_PATH=/keycloak
KC_PROXY=passthrough
EOL


echo ------------------------------------------------------------
echo ------------ 4. Build Docker Compose File ------------------
echo ------------------------------------------------------------
echo ""

cp .docker-compose.$HTTP_METHOD.yml docker-compose.yml

sed -i "s|<clientUrl>|$PUBLIC_URL|g" docker-compose.yml
sed -i "s|<hostIp>|$HOST_IP|g" docker-compose.yml


echo ""
echo ""
echo ------------------------------------------------------------
echo FINALY: Select and SPrepare tart Docker Compose ------------
echo ------------------------------------------------------------
echo ""
echo "DOCKER COMPOSE"
echo "run 'cd $HOME_DIR/docker-compose && docker-compose up --detach' to start the containers"
echo ""
echo "IMPORTANT"
echo "1. Wait for at least 10 Minutes for the containers to be up and healthy"
echo "2. Backup ALL Volumes and most important the DB and secretStorage"