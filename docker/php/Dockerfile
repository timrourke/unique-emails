FROM php:7.3.6-fpm-alpine

RUN apk add --no-cache --update \
    bash \
    wget \
    zip \
    zlib

RUN apk add --no-cache bash

RUN set -xe && \
    cd /tmp/ && \
    apk add --no-cache --update --virtual .phpize-deps $PHPIZE_DEPS

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN apk del .phpize-deps && \
    cd /tmp && \
    rm -rf /tmp/*

COPY ./conf.d/unique-emails.ini /usr/local/etc/php-fpm.d/unique-emails.ini
COPY ./conf.d/unique-emails.pool.conf /usr/local/etc/php-fpm.d/unique-emails.pool.conf

WORKDIR /var/www/api

COPY ./install-composer.sh /var/www/api/install-composer.sh

RUN /var/www/api/install-composer.sh && rm /var/www/api/install-composer.sh
