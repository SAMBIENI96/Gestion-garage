#!/bin/bash

# Générer la clé si elle n'existe pas
php artisan key:generate --force

# Vider les caches
php artisan config:clear
php artisan cache:clear

# Lancer Apache
apache2-foreground