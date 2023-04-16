FROM php:8.2-fpm-alpine

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apk update && apk add --no-cache git

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo pdo_mysql xdebug

WORKDIR /var/www/app