FROM php:8.3-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1

# Dependances systeme et bibliotheques pour SQLite, PostgreSQL et compression
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite \
    sqlite-dev \
    postgresql-dev \
    libzip-dev \
    linux-headers \
    bash \
    icu-dev \
    oniguruma-dev

# Extensions PHP
RUN docker-php-ext-install \
    pdo \
    pdo_sqlite \
    pdo_pgsql \
    zip \
    bcmath \
    opcache \
    intl \
    mbstring

# Installation de Composer v2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Installation prealable des dependances PHP pour optimiser le cache Docker
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Copie du code source complet
COPY . .

# Finalisation du chargement des classes
RUN composer dump-autoload --optimize

# Permissions des dossiers de logs, sessions et cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && chmod +x /var/www/docker/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/var/www/docker/entrypoint.sh"]
CMD ["php-fpm"]
