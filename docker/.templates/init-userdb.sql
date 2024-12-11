CREATE USER 'odc'@'%' IDENTIFIED BY '<open-datenschutzcenter-pw>';
CREATE DATABASE odc;
GRANT ALL PRIVILEGES ON odc.* TO 'odc'@'%';
CREATE USER 'keycloak'@'%' IDENTIFIED BY '<keycloak-pw>';
CREATE DATABASE keycloak;
GRANT ALL PRIVILEGES ON keycloak.* TO 'keycloak'@'%';
FLUSH PRIVILEGES;