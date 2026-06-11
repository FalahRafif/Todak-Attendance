ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-fpm-alpine AS app

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    mysql-client \
    shadow

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        zip \
        gd \
        mbstring \
        intl \
        opcache \
        pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 appuser \
    && adduser -u 1000 -G appuser -s /bin/sh -D appuser

WORKDIR /var/www/html

RUN mkdir -p \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache/data \
        storage/app/public \
        storage/logs \
        bootstrap/cache \
        /run/nginx

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/nginx/laravel.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

RUN sed -i 's/^user = .*/user = appuser/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^group = .*/group = appuser/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf

RUN chown -R appuser:appuser /var/www/html /run/nginx

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]

CMD ["supervisord", "-c", "/etc/supervisord.conf", "-n"]
