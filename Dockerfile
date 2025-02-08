ARG PHP_IMAGE_VERSION=3.20.6
FROM thecodingmachine/php:8.3-v4-fpm-node22 AS builder
ARG VERSION

COPY . /var/www/html

USER root

RUN npm install \
    && npm run build

RUN composer install --no-scripts

RUN sed -i "s/^laF_version=.*/laF_version=${VERSION}/" .env

RUN tar \
    --exclude='./.github' \
    --exclude='./.git' \
    --exclude='./node_modules' \
    --exclude='./var/cache' \
    --exclude='./var/log' \
    -zcvf /artifact.tgz .


FROM git.h2-invent.com/public-system-design/alpine-php8-cron-webserver:3.20.7
ARG VERSION

LABEL version="${VERSION}" \
    Maintainer="H2 invent GmbH" \
    Description="Docker Image for Open Datenschutzcenter" \
    org.opencontainers.version="${VERSION}" \
    org.opencontainers.image.title="Open Datenschutzcenter" \
    org.opencontainers.image.license="AGPLv3" \
    org.opencontainers.image.vendor="H2 invent GmbH" \
    org.opencontainers.image.authors="Andreas Holzmann <support@h2-invent.com>" \
    org.opencontainers.image.source="https://github.com/h2-invent/open-datenschutzcenter" \
    org.opencontainers.image.documentation="https://open-datenschutzcenter.de" \
    org.opencontainers.image.url="https://open-datenschutzcenter.de"

USER root

RUN echo "# Docker Cron Jobs" > /var/crontab \
    && echo "SHELL=/bin/sh" >> /var/crontab \
    && echo "0 9 * * 1-5 /bin/sh /distributed_cron.sh 'data/cron_log' 'php /var/www/html/bin/console app:cron'" >> /var/crontab \
    && echo "" >> /var/crontab

RUN echo "#!/bin/sh" > /docker-entrypoint-init.d/01-symfony.sh \
    && echo "php bin/console cache:clear" >> /docker-entrypoint-init.d/01-symfony.sh \
    && echo "php bin/console doc:mig:mig --no-interaction" >> /docker-entrypoint-init.d/01-symfony.sh \
    && echo "php bin/console cache:clear" >> /docker-entrypoint-init.d/01-symfony.sh \
    && chmod +x /docker-entrypoint-init.d/01-symfony.sh

USER nobody

COPY --from=builder /artifact.tgz artifact.tgz

RUN tar -zxvf artifact.tgz \
    && mkdir data \
    && mkdir -p var/log \
    && mkdir -p var/cache \
    && rm artifact.tgz

ENV nginx_root_directory=/var/www/html/public \
    upload_max_filesize=10M
