FROM registry.blumilk.pl/internal-public/secops-tools-bin:v1.3.0@sha256:7bfcc9b09c994da1cb00f7b25e13c4d71081e5494c16f2e89b55a28b673fd79f AS secops-tools-bin
FROM composer:2.10.2@sha256:d020706319701a44468968321dccd0fce6620190159a7a9ec195d78e6e971c71 AS composer
FROM ghcr.io/php/pie:1.5.1-bin@sha256:0ecfcffe6f22badd0ae9faa0279217d2aff24c4c1190b18ab82d3e1febc3877e AS pie-bin
FROM node:24.18.1-bookworm-slim@sha256:235600a8101ab264e117b1768e925532262668dc9b581ef1dd7d96ced463b8e7 AS node

FROM php:8.4.26-fpm-bookworm@sha256:43e1ac38217031dbbecae60e84ccf8593722031559178d199bf56adb0145d5d0 AS base

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=pie-bin /pie /usr/bin/pie

# For other versions check: https://nginx.org/packages/mainline/debian/pool/nginx/n/nginx/
# renovate: datasource=github-tags depName=nginx/nginx extractVersion=^release-(?<version>.*)$
ARG NGINX_VERSION="1.31.6"

# renovate: datasource=github-tags depName=phpredis/phpredis
ARG PHPREDIS_VERSION=6.3.0

RUN apt-get update \
    && apt-get install --assume-yes gpg \
    && curl https://nginx.org/keys/nginx_signing.key | gpg --dearmour --output /etc/apt/trusted.gpg.d/apt.nginx.org.gpg > /dev/null \
    && echo "deb https://nginx.org/packages/mainline/debian bookworm nginx" | tee /etc/apt/sources.list.d/nginx.list \
    && apt-get update && apt-get install --assume-yes \
        nginx=${NGINX_VERSION}-1~bookworm \
        zip \
        libzip-dev \
        supervisor \
        libpq-dev \
    && pie install phpredis/phpredis:${PHPREDIS_VERSION} \
    && docker-php-ext-install \
        pdo_pgsql 

COPY ./environment/.docker/app/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
COPY ./environment/.docker/app/nginx.conf /etc/nginx/nginx.conf

WORKDIR /application

ENTRYPOINT ["/entrypoint.sh"]

FROM base AS local

COPY --from=secops-tools-bin /usr/local/bin/age /usr/local/bin/age-keygen /usr/local/bin/sops /usr/local/bin/

RUN mv "${PHP_INI_DIR}/php.ini-development" "${PHP_INI_DIR}/php.ini"

ARG USER_NAME=host-user
ARG USER_ID=1000
ARG PHP_FPM_GROUP=www-data

RUN adduser \
        --disabled-password \
        --uid ${USER_ID} \
        ${USER_NAME} \
    && usermod \
        --append \
        --groups \
        ${PHP_FPM_GROUP} \
        ${USER_NAME}

# Add node, npm, npx binaries
COPY --from=node --chown=${USER_NAME}:root /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node --chown=${USER_NAME}:root /usr/local/bin/node /usr/local/bin/node
RUN ln --symbolic /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && chown --no-dereference ${USER_NAME}:root /usr/local/bin/npm \
    && ln --symbolic /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && chown --no-dereference ${USER_NAME}:root /usr/local/bin/npx

# renovate: datasource=github-tags depName=xdebug/xdebug
ARG XDEBUG_VERSION=3.5.3
ARG INSTALL_XDEBUG=false

RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    pie install xdebug/xdebug:${XDEBUG_VERSION} \
;fi

FROM composer AS vendor-build

WORKDIR /app_composer_dependencies

COPY composer.json composer.lock ./

RUN composer install \
        --no-interaction \
        --no-plugins \
        --no-scripts \
        --no-dev \
        --prefer-dist \
        --ignore-platform-reqs

FROM node AS frontend-build

WORKDIR /app_frontend_dependencies

ARG ENVIRONMENT_ARG
ENV ENVIRONMENT=${ENVIRONMENT_ARG}

# last position is target directory
COPY package.json \
    package-lock.json \
    postcss.config.js \
    vite.config.js \
    ./

COPY ./environment/${ENVIRONMENT}/app/vite.${ENVIRONMENT}.env .env

RUN npm clean-install

COPY resources/js ./resources/js/
COPY resources/css ./resources/css/
COPY resources/views ./resources/views/

RUN npm run build

FROM base AS production

ARG ENVIRONMENT_ARG
ENV ENVIRONMENT=${ENVIRONMENT_ARG}

RUN docker-php-ext-install \
    opcache

RUN mv "${PHP_INI_DIR}/php.ini-production" "${PHP_INI_DIR}/php.ini"

COPY ./environment/${ENVIRONMENT}/app/php.ini ${PHP_INI_DIR}/conf.d/zzz-overrides-php.ini
COPY ./environment/${ENVIRONMENT}/app/php-fpm.conf /usr/local/etc/php-fpm.d/zzz-overrides.conf
COPY ./environment/${ENVIRONMENT}/app/supervisord.conf /etc/supervisor/custom-supervisord.conf

# Copy Frontend build
COPY --chown=www-data:www-data --from=frontend-build /app_frontend_dependencies/public/build ./public/build/

# Copy Composer dependencies
COPY --chown=www-data:www-data --from=vendor-build /app_composer_dependencies/vendor ./vendor/

# Copy App files
COPY --chown=www-data:www-data . .

# Remove unnecessary files
RUN rm --recursive --force \
    postcss.config.js \
    vite.config.js \
    .dockerignore \
    environment/.docker \
    environment/dev

RUN composer dump-autoload --optimize

EXPOSE 80

ARG PROJECT_BUILD_VERSION_ARG
ENV PROJECT_BUILD_VERSION=${PROJECT_BUILD_VERSION_ARG}
