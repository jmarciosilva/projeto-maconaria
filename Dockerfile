# syntax=docker/dockerfile:1
#
# Imagem para uso exclusivamente local (ambiente de desenvolvimento em casa).
# Mantém as mesmas versões descritas em "Requisitos do ambiente" no README.md
# (PHP 8.3.30, Composer 2.9+, Node.js 22+/npm 10+, MySQL 8 — este último
# provisionado à parte pelo docker-compose.yml).

# ---- Base: PHP-FPM 8.3.30 com as extensões exigidas pelo composer.json ----
FROM php:8.3.30-fpm-alpine AS php-base

# UID/GID do usuário do host: o código-fonte é montado por bind mount
# (docker-compose.yml) para permitir edição ao vivo, então o processo
# php-fpm precisa rodar com o mesmo UID do seu usuário para poder escrever
# em storage/ e bootstrap/cache/ (o `chown` feito no build da imagem some
# assim que o bind mount sobrepõe o diretório). Ajuste via
# `docker compose build --build-arg WWW_UID=$(id -u) --build-arg WWW_GID=$(id -g)`
# se o seu usuário local não for 1000:1000.
ARG WWW_UID=1000
ARG WWW_GID=1000

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        shadow \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        bcmath \
        exif \
        gd \
        intl \
        pcntl \
        zip \
    && groupmod -g "${WWW_GID}" www-data \
    && usermod -u "${WWW_UID}" -g "${WWW_GID}" www-data

COPY --from=composer:2.9 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---- Etapa 1: dependências PHP (Composer 2.9) ----
# Roda sobre a mesma base do PHP-FPM final para que as extensões nativas
# (ex.: ext-bcmath, exigida por laravel-lang) fiquem visíveis ao composer.
# --no-scripts: no build da imagem ainda não há MySQL acessível (só existe
# depois que o docker-compose sobe o serviço `mysql`), e o hook
# post-autoload-dump roda `php artisan package:discover`, que boota a
# aplicação e consulta o banco (AppServiceProvider -> AplicadorConfiguracaoEmail).
# O cache de pacotes é gerado normalmente por Laravel na primeira requisição
# em tempo de execução, já com o MySQL do compose disponível.
FROM php-base AS vendor
COPY . .
RUN composer install --no-interaction --prefer-dist --no-scripts

# ---- Etapa 2: assets front-end (Node.js 22 + npm) ----
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# ---- Etapa 3: imagem final ----
FROM php-base AS app

COPY docker/php/php.ini /usr/local/etc/php/conf.d/local.ini

COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
