FROM spiralscout/roadrunner:2.1.1 as rr
FROM php:8-fpm-alpine:3.13

LABEL maintainer="Sam Maosa <maosa.sam@gmail.com>"

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
        pdo_pgsql \
        pdo_dblib \
        sockets \
        zip \
        xml \
        pcntl
# Enable opcache
RUN docker-php-ext-enable opcache
# Install PECL extensions
#RUN pecl install memcached && \
#    docker-php-ext-enable memcached

RUN pecl install redis
RUN docker-php-ext-enable redis

# Install Extensions (Swoole)
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync
RUN install-php-extensions swoole

RUN apk del build-dependencies

WORKDIR /app

COPY .docker/config /tmp/config
# Download trusted certs
RUN mkdir -p /etc/ssl/certs && update-ca-certificates && \
# Copy supervisor config
    mkdir /etc/supervisor.d && \
    cp /tmp/config/supervisord.conf /etc/supervisord.conf && \
    cp -r /tmp/config/supervisor.d /etc && \
# Copy cron job
    cp /tmp/config/crontab /etc/crontabs/www-data && \
    cp /tmp/config/entrypoint.sh /entrypoint.sh && \
    rm -rf /tmp/config && \
    chmod a+x /entrypoint.sh

# Copy RoadRunner
COPY --from=rr /usr/bin/rr /usr/bin/rr
COPY --from=rr /usr/bin/rr ./rr

# Copy RoadRunner config
COPY --from=rr /etc/rr.yaml /etc/rr.yaml
COPY --from=rr /etc/rr.yaml ./.rr.yaml

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
#Add octane state log

#Make rr executable
RUN chmod 774 ./rr
ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 8000
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
