# syntax=docker/dockerfile:1.7
ARG COMPOSER_IMAGE=composer:2@sha256:0049ce90b432795bf413a738b073963ada1a689e1a8708104684baafe94586ec
ARG PHP_IMAGE=php:8.5.13-fpm-alpine3.24@sha256:51414d5291758fe27f58a64c7e8a078465d32cc7dcef7bda803f5b36019802e0

FROM ${COMPOSER_IMAGE} AS composer

FROM ${PHP_IMAGE}

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# PHP 8.5.13: uri, pdo_sqlite e opcache já presentes na imagem oficial.
RUN apk add --no-cache git unzip icu-libs libpq \
 && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
 && mkdir -p /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf
WORKDIR /var/www/html

# O código é montado em dev; em produção, copie e rode composer install --no-dev.
CMD ["php-fpm", "-F"]
