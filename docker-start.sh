#!/bin/bash
set -e

echo "Demarrage AutoGest Pro..."

# Vider les caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Migrer ET seeder automatiquement
php artisan migrate --force
php artisan db:seed --force

# Storage link
php artisan storage:link

echo "Serveur pret !"
apache2-foreground