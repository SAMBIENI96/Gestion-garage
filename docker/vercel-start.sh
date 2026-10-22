#!/bin/sh
# Démarrage du conteneur Vercel.
# En cas de problème de configuration, au lieu de planter (erreur 500 muette),
# le conteneur sert une page de diagnostic qui explique la cause.

PORT="${PORT:-80}"

diagnostic() {
    # Masque les identifiants éventuels (user:motdepasse@ et password=…)
    detail=$(printf '%s' "$2" | sed -E 's#://[^:/@]+:[^@]+@#://***:***@#g; s#(password|pwd)=[^& ]+#\1=***#gi' | tail -n 15)
    echo "ERREUR : $1" >&2
    [ -n "$detail" ] && echo "$detail" >&2

    mkdir -p /tmp/diag
    printf '%s' "$1" > /tmp/diag/cause.txt
    printf '%s' "$detail" > /tmp/diag/detail.txt
    cat > /tmp/diag/index.php <<'PHP'
<?php
http_response_code(503);
$cause  = htmlspecialchars(file_get_contents(__DIR__.'/cause.txt'));
$detail = htmlspecialchars(file_get_contents(__DIR__.'/detail.txt'));
?><!DOCTYPE html><html lang="fr"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>AutoGest Pro — configuration incomplète</title>
<body style="font-family:system-ui,sans-serif;max-width:720px;margin:48px auto;padding:0 16px;color:#101522;background:#f4f5f8">
<h1 style="font-size:22px">⚠️ L'application ne peut pas démarrer</h1>
<p style="background:#fff;border:1px solid #fecaca;border-left:4px solid #dc2626;padding:14px 16px;border-radius:8px"><?= $cause ?></p>
<?php if ($detail): ?><h2 style="font-size:15px">Détail technique</h2>
<pre style="white-space:pre-wrap;background:#101522;color:#eef0f6;padding:14px;border-radius:8px;font-size:12.5px"><?= $detail ?></pre><?php endif; ?>
<p style="color:#4a5368;font-size:14px">Corriger dans Vercel → <b>Settings → Environment Variables</b>, puis <b>Deployments → ⋯ → Redeploy</b>.</p>
</body></html>
PHP
    exec frankenphp php-server --root /tmp/diag --listen ":$PORT"
}

[ -n "$APP_KEY" ] || diagnostic "La variable APP_KEY est absente. Générez-la avec « php artisan key:generate --show » et ajoutez-la dans Vercel."

if [ -z "$DATABASE_URL" ] && [ -z "$DB_HOST" ]; then
    diagnostic "La variable DATABASE_URL est absente. Collez l'adresse de connexion de votre base Neon (sans « -pooler »)."
fi

# Migrations idempotentes : ne font rien si la base est déjà à jour
out=$(php artisan migrate --force --no-interaction 2>&1) \
    || diagnostic "Impossible de préparer la base de données (connexion ou migration refusée). Vérifiez DATABASE_URL." "$out"
echo "$out"

# Comptes de démo seulement si DEMO_SEED=true et base vide
if [ "$DEMO_SEED" = "true" ]; then
    out=$(php artisan demo:seed 2>&1) || diagnostic "Échec de la création des comptes de démonstration." "$out"
    echo "$out"
fi

exec frankenphp run --config /etc/frankenphp/Caddyfile
