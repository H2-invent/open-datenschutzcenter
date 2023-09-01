FROM thecodingmachine/php:8.2-v4-apache-node16
ENV PHP_EXTENSION_LDAP=1
ENV PHP_EXTENSION_INTL=1
ENV TZ=Europe/Berlin
ENV COMPOSER_ALLOW_SUPERUSER=1
USER root
RUN usermod -a -G www-data docker
#Do npm install
COPY package.json /var/www/html
COPY package-lock.json /var/www/html
COPY webpack.config.js /var/www/html
RUN npm install
#do npm build
COPY assets /var/www/html/assets
COPY public /var/www/html/public
RUN mkdir -m 777 -p public/build
RUN npm run build
RUN rm -rf node_modules/
#copy all the rest of the app
COPY . /var/www/html
#install all php dependencies

RUN chown -R docker:docker secretStorage
USER docker
RUN composer install
USER root
#do all the directory stuff
RUN chmod -R 775 public/build
RUN mkdir -p var/cache
RUN chown -R docker:docker var
RUN chmod -R 777 var
USER docker