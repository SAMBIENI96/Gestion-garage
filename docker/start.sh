#!/bin/bash
set -e

echo "Démarrage AutoGest Pro..."

if [ -z "$APP_KEY" ]; then
    echo "ERREUR : la variable APP_KEY est absente (générer avec : php artisan key:generate --show)" >&2
    exit 1
fi

# Apache écoute sur le port imposé par l'hébergeur
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Mode « tout-en-un » : base SQLite dans le conteneur, aucun service externe.
# Le disque étant éphémère, la base repart de zéro à chaque démarrage.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    touch "$DB_DATABASE"
    chown www-data:www-data "$DB_DATABASE" "$(dirname "$DB_DATABASE")"
fi

# Cache de configuration (lit les variables d'environnement du conteneur)
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Migrations..."
php artisan migrate --force

# Comptes de démonstration : uniquement si DEMO_SEED=true et si la base est vide
# (ne réécrit jamais des mots de passe déjà changés)
if [ "$DEMO_SEED" = "true" ]; then
    php artisan demo:seed
fi

php artisan storage:link >/dev/null 2>&1 || true

echo "Prêt sur le port ${PORT}"
exec apache2-foreground
