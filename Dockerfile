FROM thecodingmachine/php:7.4.27-v4-apache-node16
USER root
COPY . /var/www/html
RUN npm install
RUN composer install
RUN npm --unsafe-perm --user root run build
RUN mkdir -p var/cache
RUN chown -R www-data:www-data var
RUN chmod -R 775 var
RUN mkdir -p public/uploads/images
RUN chown -R www-data:www-data public/uploads/images
RUN chmod -R 775 public/uploads/images

USER docker
