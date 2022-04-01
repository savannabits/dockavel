FROM php:fpm-alpine

LABEL maintainer="Sam Maosa <maosa.sam@gmail.com>"

WORKDIR /app
# Install system packages
RUN apk update && \
    apk add --no-cache \
        bash \
        git \
        curl \
        nano \
        g++ \
        sudo \
        freetds \
        freetype \
        icu \
        libintl \
        libldap \
        libjpeg \
        libpng \
        libpq \
        libwebp \
        libmemcached \
        supervisor \
        libzip \
        composer && \
    apk add --no-cache --virtual build-dependencies \
        curl-dev \
        freetds-dev \
        freetype-dev \
        gettext-dev \
        icu-dev \
        jpeg-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
        libmemcached-dev \
        openldap-dev \
        postgresql-dev \
        zlib-dev \
        autoconf \
        build-base
# Install PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype=/usr/include/ \
        --with-jpeg=/usr/include/ && \
    docker-php-ext-configure ldap --with-libdir=lib/ && \
    docker-php-ext-configure pdo_dblib --with-libdir=lib/ && \
    docker-php-ext-install \
        bcmath \
        curl \
        ctype \
        fileinfo \
        gettext \
        gd \
        exif \
        iconv \
        intl \
        tokenizer \
        ldap \
        opcache \
        pdo_mysql \
        pdo_sqlite \
        pdo_pgsql \
        pdo_dblib \
        soap \
        sockets \
        zip \
        xml \
        pcntl
# Install PECL extensions
RUN pecl install memcached && \
    docker-php-ext-enable memcached

RUN pecl install redis
RUN docker-php-ext-enable redis

RUN apk del build-dependencies

COPY .docker/config /tmp/config
# Download trusted certs
RUN mkdir -p /etc/ssl/certs && update-ca-certificates && \
# Copy php configuration
    cp /tmp/config/php.ini /usr/local/etc/php/php.ini && \
# Copy supervisor config
    mkdir /etc/supervisor.d && \
    cp /tmp/config/supervisord.conf /etc/supervisord.conf && \
    cp -r /tmp/config/supervisor.d /etc && \
# Copy cron job
    cp /tmp/config/crontab /etc/crontabs/www-data && \
    cp /tmp/config/entrypoint.sh /entrypoint.sh && \
    rm -rf /tmp/config && \
    chmod a+x /entrypoint.sh

ENV ENABLE_CRON_JOB=${ENABLE_CRON_JOB:-false}
ENV ENABLE_LARAVEL_WORKER=${ENABLE_LARAVEL_WORKER:-false}
ENV ENABLE_LARAVEL_HORIZON=${ENABLE_LARAVEL_HORIZON:-false}
ENV ENABLE_LARAVEL_WEBSOCKETS=${ENABLE_LARAVEL_WEBSOCKETS:-false}
ARG UID=1000
ARG USER='developer'
#Create non-root sudoer
RUN adduser -S -D -G www-data -u $UID -h /home/$USER $USER
RUN mkdir -p /home/$USER/.composer && \
    chown -R $USER:www-data /home/$USER
RUN chown -R $USER:www-data /app
EXPOSE 6001
ENTRYPOINT ["/entrypoint.sh"]

CMD ["php-fpm", "-F"]
