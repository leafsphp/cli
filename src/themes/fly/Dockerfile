#!/usr/bin/env bash

# syntax = docker/dockerfile:experimental

# Default to PHP 8.2, but we attempt to match
# the PHP version from the user (wherever `flyctl launch` is run)
# Valid version values are PHP 7.4+
ARG PHP_VERSION=8.2
ARG NODE_VERSION=18
FROM --platform=linux/amd64 mychidarko/leaf-fly-fpm:${PHP_VERSION} as base
LABEL fly_launch_runtime="leaf"

# PHP_VERSION needs to be repeated here
# See https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION
ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer \
    COMPOSER_MAX_PARALLEL_HTTP=24 \
    PHP_PM_MAX_CHILDREN=10 \
    PHP_PM_START_SERVERS=3 \
    PHP_MIN_SPARE_SERVERS=2 \
    PHP_MAX_SPARE_SERVERS=4 \
    PHP_DATE_TIMEZONE=UTC \
    PHP_DISPLAY_ERRORS=Off \
    PHP_ERROR_REPORTING=22527 \
    PHP_MEMORY_LIMIT=256M \
    PHP_MAX_EXECUTION_TIME=90 \
    PHP_POST_MAX_SIZE=100M \
    PHP_UPLOAD_MAX_FILE_SIZE=100M \
    PHP_ALLOW_URL_FOPEN=Off

# Prepare base container:
# 1. Install PHP, Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY .fly/php/ondrej_ubuntu_php.gpg /etc/apt/trusted.gpg.d/ondrej_ubuntu_php.gpg
ADD .fly/php/packages/${PHP_VERSION}.txt /tmp/php-packages.txt

RUN apt-get update
RUN apt-get install -y --no-install-recommends gnupg2 ca-certificates git-core curl zip unzip \
    rsync vim-tiny htop sqlite3 nginx supervisor cron mysql-server postgresql postgresql-client \
    libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libxml2-dev

RUN ln -sf /usr/bin/vim.tiny /etc/alternatives/vim \
    && ln -sf /etc/alternatives/vim /usr/bin/vim

RUN apt-get update && apt-get install -y wget gnupg && \
    echo "deb http://apt.postgresql.org/pub/repos/apt/ focal-pgdg main" > /etc/apt/sources.list.d/pgdg.list && \
    wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -

RUN apt-get update && apt-get install -y libpq-dev postgresql-client

RUN echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu focal main" > /etc/apt/sources.list.d/ondrej-ubuntu-php-focal.list \
    && apt-get update \
    && apt-get -y --no-install-recommends install $(cat /tmp/php-packages.txt) \
    && ln -sf /usr/sbin/php-fpm${PHP_VERSION} /usr/sbin/php-fpm \
    && mkdir -p /var/www/html/public && echo "index" > /var/www/html/public/index.php \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# 2. Copy config files to proper locations
COPY .fly/nginx/ /etc/nginx/
COPY .fly/fpm/ /etc/php/${PHP_VERSION}/fpm/
COPY .fly/supervisor/ /etc/supervisor/
COPY .fly/entrypoint.sh /entrypoint
COPY .fly/start-nginx.sh /usr/local/bin/start-nginx
RUN chmod 754 /usr/local/bin/start-nginx

# copy application code, skipping files based on .dockerignore
COPY . /var/www/html
WORKDIR /var/www/html

RUN composer install --optimize-autoloader --no-dev \
    # && mkdir -p storage/logs \
    && chown -R www-data:www-data /var/www/html \
    # && sed -i 's/protected \$proxies/protected \$proxies = "*"/g' app/Http/Middleware/TrustProxies.php \
    # && echo "MAILTO=\"\"\n* * * * * www-data /usr/bin/php /var/www/html/artisan schedule:run" > /etc/cron.d/laravel \
    && cp .fly/entrypoint.sh /entrypoint \
    && chmod +x /entrypoint \
    && touch .env
    # \
    # && supervisorctl reread \
    # && supervisorctl update \
    # && supervisorctl start redis

# Multi-stage build: Build static assets
# This allows us to not include Node within the final container
FROM node:${NODE_VERSION} as node_modules_go_brrr

RUN mkdir /app

RUN mkdir -p  /app
WORKDIR /app
COPY . .
COPY --from=base /var/www/html/vendor /app/vendor

# Use yarn or npm depending on what type of
# lock file we might find. Defaults to
# NPM if no lock file is found.
RUN if [ -f "yarn.lock" ]; then \
    yarn install --frozen-lockfile; \
    yarn build; \
    elif [ -f "pnpm-lock.yaml" ]; then \
    corepack enable && corepack prepare pnpm@latest-7 --activate; \
    pnpm install --frozen-lockfile; \
    pnpm run build; \
    elif [ -f "package-lock.json" ]; then \
    npm ci --no-audit; \
    npm run build; \
    else \
    # if no lock file is found, we check if we have a package.json
    # and if so, we run npm install
    if [ -f "package.json" ]; then \
    npm install --force; \
    npm run build; \
    fi; \
    fi;

# From our base container created above, we
# create our final image, adding in static
# assets that we generated above
FROM base

# Packages like Laravel Nova may have added assets to the public directory
# or maybe some custom assets were added manually! Either way, we merge
# in the assets we generated above rather than overwrite them
COPY --from=node_modules_go_brrr /app/public /var/www/html/public-npm
RUN rsync -ar /var/www/html/public-npm/ /var/www/html/public/ \
    && rm -rf /var/www/html/public-npm \
    && chown -R www-data:www-data /var/www/html/public \
    # && php leaf db:migrate \
    && php leaf link \
    && rm -rf public/hot

# RUN rm -rf /etc/nginx/sites-enabled/default
# RUN unlink /etc/nginx/sites-enabled/default

# COPY --from=base /var/www/html/default /etc/nginx/sites-enabled/default
# RUN ln -s /var/www/html/default /etc/nginx/sites-enabled/

EXPOSE 8080

ENTRYPOINT ["/entrypoint"]
