# syntax=docker/dockerfile:1
#
# Dockerfile multi-stage para produção do ARLS Ferraz
# Baseado na arquitetura de produção do projeto feira-esquerda-livre
#
# Etapas:
#   1. vendor     - Install Composer dependencies
#   2. assets     - Build Vite frontend assets
#   3. php        - PHP-FPM application server
#   4. web        - Nginx reverse proxy + static files

# ---- Etapa 1: vendor (Composer dependencies) ----
FROM php:8.3-cli-bookworm AS vendor

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock ./

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --no-scripts \
      --optimize-autoloader


# ---- Etapa 2: assets (Vite build) ----
FROM node:22-bookworm AS assets

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

RUN npm run build


# ---- Etapa 3: php (PHP-FPM final) ----
FROM php:8.3-fpm-bookworm AS php

WORKDIR /var/www/html

# Instalar extensões PHP obrigatórias
RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pcntl \
    pdo_mysql

# Configuração PHP para produção
COPY deploy/php.ini $PHP_INI_DIR/conf.d/99-production.ini

# Cópias do build (não bind mounts)
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Symlink para storage público
RUN ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Permissões adequadas (sem chmod 777)
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]


# ---- Etapa 4: web (Nginx reverse proxy) ----
FROM nginx:1.27-alpine AS web

# Copiar aplicação compilada do estágio php
COPY --from=php /var/www/html/public /var/www/html/public
COPY --from=php /var/www/html/storage /var/www/html/storage

# Configuração Nginx para produção
COPY deploy/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
