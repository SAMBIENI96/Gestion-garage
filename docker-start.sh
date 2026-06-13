#!/bin/bash
set -e

echo "Demarrage AutoGest Pro..."

# Variables
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Migrer
php artisan migrate --force --seed

# Storage link
php artisan storage:link

echo "Serveur pret !"
apache2-foreground