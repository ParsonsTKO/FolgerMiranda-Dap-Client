# Building Production image
FROM php:7.2.12-fpm-alpine as prod
LABEL maintainer="FOLGER" description="FOLGER DAPClient App" environment="Production"

WORKDIR /app

RUN docker-php-ext-install opcache && \
    mkdir -m 0755 var && \
    chown www-data var && \
    mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

ARG COMPOSER_VERSION=1.7.3
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin --version=$COMPOSER_VERSION --filename=composer    

ENV SYMFONY_ENV=prod
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_CACHE_DIR=/dev/null
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_PROCESS_TIMEOUT=3600

COPY etc/docker/app/production.con[f] /usr/local/etc/php-fpm.d/zz-production.conf
COPY etc/docker/app/production.ini /usr/local/etc/php/conf.d/zz-production.ini
COPY composer.json composer.loc[k] ./

RUN composer validate --no-check-all --strict && \
    composer install --prefer-dist --no-autoloader --no-suggest --no-interaction --no-scripts --no-dev

COPY bin/console ./bin/console
COPY web/app.php ./web/app.php
COPY app ./app
COPY src ./src

RUN composer dump-autoload --optimize --no-interaction --classmap-authoritative

# Building Development image
FROM prod as dev
LABEL environment="Development"

ENV SYMFONY_ENV=dev

RUN mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
COPY ./etc/docker/app/development.ini /usr/local/etc/php/conf.d/zzz-development.ini

RUN composer install --prefer-dist --no-autoloader --no-suggest --no-interaction && \
    composer dump-autoload --optimize --no-interaction

# Building Development with XDebug image
FROM dev as dev-xdebug
LABEL environment="Development with XDebug"

RUN apk --update --no-cache add autoconf g++ make && \
    pecl install -f xdebug && \
    docker-php-ext-enable xdebug && \
    apk del --purge autoconf g++ make

COPY ./etc/docker/app/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini   