FROM php:8.2-apache

RUN apt update \
    && apt install -y build-essential curl zlib1g-dev g++ git libicu-dev zip libzip-dev \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev libssl-dev pkg-config \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg \
    && apt clean

# Installer l'extension MongoDB PHP
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Activer le module mod_rewrite d'Apache
RUN a2enmod rewrite

COPY ./docker/config/apache/default.conf /etc/apache2/sites-enabled/000-default.conf

# Définir le répertoire de travail
WORKDIR /var/www

# Copier le code source complet
COPY . .

# Installer composer
RUN curl -sS https://getcomposer.org/download/2.8.5/composer.phar -o /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer


# Installer les dépendances avec Composer
RUN composer install --optimize-autoloader --no-scripts


# Fixer les droits pour Apache
RUN chown -R www-data:www-data /var/www && chmod -R 777 /var/www

RUN mkdir -p var/cache/prod \
    && mkdir -p var/log
RUN chmod 777 ./var/cache/prod
RUN chmod 777 ./var/log

# Exposer le port 80 pour Apache
EXPOSE 80