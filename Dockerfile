# Image de production AutoGest Pro : PHP 8.3 + Apache.
# Base de données : SQLite dans le conteneur par défaut (démo réinitialisée à chaque
# démarrage), ou PostgreSQL externe avec DB_CONNECTION=pgsql et DATABASE_URL.
# Le CSS/JS est statique (public/css, public/js) : aucune étape Node n'est nécessaire.
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libzip-dev unzip \
    && docker-php-ext-install pdo_pgsql zip bcmath opcache \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache : racine sur public/, réécriture d'URL pour Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite headers

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-autogest.ini

WORKDIR /var/www/html

# Dépendances PHP d'abord (couche mise en cache tant que composer.lock ne change pas)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# L'hébergeur fournit PORT ; 8080 par défaut
ENV PORT=8080
EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]
