#!/bin/bash
set -e

echo "🚀 Demarrage AutoGest Pro..."

# ── Clear caches (sécurisé production)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# ── Migrations (obligatoire en prod)
echo "📦 Migration database..."
php artisan migrate --force

# ── Seeder (SAFE: ne casse pas le deploy si doublons)
echo "🌱 Seeding database..."
php artisan db:seed --force || true

# ── Storage link (évite crash si déjà existant)
echo "🔗 Storage link..."
php artisan storage:link || true

# ── Optimisation production (important)
echo "⚡ Optimisation..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Serveur pret !"

apache2-foreground