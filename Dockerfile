ARG PHP_IMAGE_VERSION=3.20.6
FROM git.h2-invent.com/public-system-design/alpine-php8-webserver:${PHP_IMAGE_VERSION}

ARG VERSION
ARG SUPERCRONIC_VERSION=0.2.33

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

RUN apk --no-cache add \
    unzip \
    && rm -rf /var/cache/apk/*

RUN mkdir /etc/service/cron \
    && echo "#!/bin/sh" > /etc/service/cron/run \
    && echo "exec 2>&1 /supercronic /var/crontab" >> /etc/service/cron/run \
    && chown -R nobody:nobody /etc/service/cron \
    && chmod -R +x /etc/service/cron

RUN wget https://github.com/aptible/supercronic/releases/download/v${SUPERCRONIC_VERSION}/supercronic-linux-amd64 -O /supercronic \
    && chmod +x /supercronic

RUN wget https://git.h2-invent.com/Public-System-Design/Public-Helperscripts/raw/branch/main/distributed_cron.sh -O /distributed_cron.sh \
    && chmod +x /distributed_cron.sh

RUN echo "# Docker Cron Jobs" > /var/crontab \
    && echo "SHELL=/bin/sh" >> /var/crontab \
    && echo "* * * * * date" >> /var/crontab \
    && echo "0 1 * * * curl https://open-datenschutzcenter.de/health/check" >> /var/crontab \
    && echo "0 9 * * 1-5 /bin/sh /distributed_cron.sh 'data/cron_log' 'php /var/www/html/bin/console app:cron'" >> /var/crontab \
    && echo "" >> /var/crontab

RUN echo "#!/bin/sh" > /docker-entrypoint-init.d/01-symfony.sh \
    && echo "php bin/console cache:clear" >> /docker-entrypoint-init.d/01-symfony.sh \
    && echo "php bin/console doc:mig:mig --no-interaction" >> /docker-entrypoint-init.d/01-symfony.sh \
    && echo "php bin/console cache:clear" >> /docker-entrypoint-init.d/01-symfony.sh \
    && chmod +x /docker-entrypoint-init.d/01-symfony.sh

USER nobody

RUN wget https://github.com/H2-invent/open-datenschutzcenter/releases/download/${VERSION}/application.zip -O artifact.zip \
    && unzip artifact.zip \
    && mkdir data \
    && rm -r var/cache \
    && rm artifact.zip

ENV nginx_root_directory=/var/www/html/public \
    upload_max_filesize=10M
