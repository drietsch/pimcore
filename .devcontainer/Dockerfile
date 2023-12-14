ARG PHP_VERSION="8.2"
ARG DEBIAN_VERSION="bookworm"

FROM php:${PHP_VERSION}-fpm-${DEBIAN_VERSION} AS pimcore_php_min

COPY --chmod=0755 files/build-*.sh /usr/local/bin/

RUN set -eux; \
    \
    DPKG_ARCH="$(dpkg --print-architecture)"; \
    echo "deb http://deb.debian.org/debian bookworm-backports main" > /etc/apt/sources.list.d/backports.list; \
    apt-get update; \
    apt-get upgrade -y; \
    \
    # tools used by Pimcore
    apt-get install -y \
        iproute2 \
        unzip \
    ; \
    \
    # dependencies for building PHP extensions
    apt-get install -y \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        zlib1g-dev \
    ; \
    \
    docker-php-ext-configure gd --enable-gd --with-jpeg; \
    docker-php-ext-configure pcntl --enable-pcntl; \
    docker-php-ext-install \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        sockets \
        zip \
    ; \
    \
    build-cleanup.sh; \
    \
    { \
        echo "upload_max_filesize = 100M"; \
        echo "memory_limit = 256M"; \
        echo "post_max_size = 100M"; \
    } > /usr/local/etc/php/conf.d/20-pimcore.ini; \
    \
    ldconfig /usr/local/lib; \
    \
    sync

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_MEMORY_LIMIT -1
COPY --from=composer/composer:2-bin /composer /usr/local/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]