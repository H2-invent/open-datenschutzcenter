FROM thecodingmachine/php:7.4.27-v4-apache-node16
USER root
RUN usermod -a -G www-data docker
COPY . /var/www/html/
RUN npm install
RUN composer install
RUN ./build.sh
USER docker
