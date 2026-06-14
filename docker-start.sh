#!/bin/bash
set -e

echo "Demarrage AutoGest Pro..."

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force
php artisan db:seed --force

php artisan storage:link

echo "Serveur pret !"
apache2-foreground