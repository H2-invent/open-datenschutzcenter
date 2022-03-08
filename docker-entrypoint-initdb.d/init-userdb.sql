CREATE USER 'datenschutzcenter'@'%' IDENTIFIED BY 'test';
CREATE DATABASE datenschutzcenter;
GRANT ALL PRIVILEGES ON datenschutzcenter.* TO 'datenschutzcenter'@'%';
CREATE USER 'keycloak'@'%' IDENTIFIED BY 'test';
CREATE DATABASE keycloak;
GRANT ALL PRIVILEGES ON keycloak.* TO 'keycloak'@'%';
FLUSH PRIVILEGES;
