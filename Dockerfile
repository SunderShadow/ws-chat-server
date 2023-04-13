FROM phpswoole/swoole:5.0-php8.2-alpine

RUN apk update && \
    apk add libpq-dev
# PHP extentions
RUN docker-php-ext-install pdo pdo_pgsql pgsql

WORKDIR /var/www
