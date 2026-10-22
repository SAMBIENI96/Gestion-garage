#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
    echo "ERREUR : APP_KEY absente (Vercel → Settings → Environment Variables)" >&2
    exit 1
fi
if [ -z "$DATABASE_URL" ] && [ -z "$DB_HOST" ]; then
    echo "ERREUR : aucune base PostgreSQL configurée (DATABASE_URL). Ajouter Neon depuis Vercel → Storage." >&2
    exit 1
fi

# Migrations idempotentes : ne font rien si la base est déjà à jour
php artisan migrate --force --no-interaction

# Comptes de démo seulement si DEMO_SEED=true et base vide
if [ "$DEMO_SEED" = "true" ]; then
    php artisan demo:seed
fi

exec frankenphp run --config /etc/frankenphp/Caddyfile
