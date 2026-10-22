# Gestion-garage
🔧 Application web de gestion de garage automobile — clients, réparations, facturation et stock en un seul endroit.

## Stack technique
- **Backend** : Laravel 12
- **Frontend Web** : Blade + CSS statique (thèmes clair et sombre, sans build npm)
- **PWA Mécanicien** : HTML / CSS / JS pur
- **Base de données** : SQLite (local et démo), PostgreSQL en option
- **Auth API** : Laravel Sanctum

## Fonctionnalités
- Gestion des clients et véhicules
- Ordres de réparation avec assignation aux mécaniciens
- Planning global de l'atelier
- Devis et facturation PDF
- Tableau de bord patron avec statistiques
- PWA mécanicien accessible via navigateur et installable sur téléphone

## Comptes de démonstration
Créés par `php artisan db:seed` (local) ou au premier démarrage du conteneur avec `DEMO_SEED=true`.

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Patron | patron@garage.local | password |
| Accueil | accueil@garage.local | password |
| Mécanicien | kofi@garage.local | password |

> Instance de **démonstration** uniquement : ne pas y saisir de vraies données clients.

## Installation
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

En local, la base est SQLite (`database/database.sqlite`, à créer avec `touch`).

## Déploiement sur Vercel (gratuit)

Vercel exécute l'application dans un conteneur FrankenPHP ([Dockerfile.vercel](Dockerfile.vercel), [vercel.json](vercel.json), [Caddyfile](Caddyfile)).
Comme Vercel peut lancer plusieurs instances qui ne partagent pas leur disque, une base **PostgreSQL externe est obligatoire** : Neon, installable en un clic depuis Vercel.

1. Sur [vercel.com](https://vercel.com) → *Add New… → Project* → importer le dépôt GitHub `Gestion-garage`. Laisser *Framework Preset* sur **Other** et le dossier racine sur `./`.
2. Avant de déployer, *Storage* → *Create Database* → **Neon** (Postgres) → région **Frankfurt** → connecter au projet. Vercel ajoute automatiquement `DATABASE_URL`.
3. *Settings → Environment Variables* :

   | Variable | Valeur |
   |---|---|
   | `APP_KEY` | résultat de `php artisan key:generate --show` — **ne jamais la changer ensuite** |
   | `APP_URL` | `https://<votre-projet>.vercel.app` |
   | `DEMO_SEED` | `true` (crée les comptes de démo si la base est vide) |
   | `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | facultatif (voir plus bas) ; URI de redirection Google : `https://<votre-projet>.vercel.app/auth/google/callback` |

4. *Deploy*. Au démarrage, le conteneur applique les migrations puis crée les comptes de démo. Chaque `git push` sur `main` redéploie automatiquement.

**Limites :** offre Hobby réservée à un usage personnel / non commercial ; les **photos d'intervention ne sont pas conservées** (disque non durable — à brancher sur Vercel Blob plus tard).

## Alternative : SnapDeploy (gratuit, sans carte bancaire)

L'application tourne dans un conteneur Docker ([Dockerfile](Dockerfile)) hébergé sur [SnapDeploy](https://snapdeploy.dev).
Par défaut, **aucune base externe** : une base SQLite est créée dans le conteneur et remplie avec les comptes de démo à chaque démarrage.

### Mise en ligne
1. Sur [snapdeploy.dev](https://snapdeploy.dev), *New container* → *GitHub* → choisir ce dépôt, branche `main`. Le `Dockerfile` à la racine est utilisé, port **8080**.
2. Variables d'environnement (runtime) :

   | Variable | Valeur |
   |---|---|
   | `APP_KEY` | résultat de `php artisan key:generate --show` (secret) |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_URL` | l'URL donnée par SnapDeploy, en `https://` |
   | `SESSION_DRIVER` | `database` |
   | `CACHE_STORE` | `database` |
   | `LOG_CHANNEL` | `stderr` |
   | `DEMO_SEED` | `true` |
   | `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | facultatif, voir « Inscription et connexion avec Google » |

3. Déployer, puis se connecter avec un compte de démo.

### Bon à savoir
- **La démo se réinitialise** à chaque redémarrage ou réveil : tout ce qui a été saisi (clients, factures, photos) est effacé et les comptes de démo sont recréés.
- L'application **s'endort après ~15 min** sans visite et met environ une minute à se réveiller.
- Offre gratuite : 100 heures de fonctionnement par mois.

### Option : conserver les données
Pour que les données survivent aux redémarrages, brancher une base PostgreSQL gratuite (Neon, Supabase, Aiven…) en ajoutant :

| Variable | Valeur |
|---|---|
| `DB_CONNECTION` | `pgsql` |
| `DATABASE_URL` | l'URL de connexion fournie par le service, avec `?sslmode=require` (secret) |

Les comptes de démo ne sont alors créés qu'au premier démarrage, quand la base est vide.

### Tester l'image en local
```bash
docker build -t autogest .
docker run -p 8080:8080 -e APP_KEY=... -e DEMO_SEED=true autogest
```

## Inscription et connexion avec Google

- **Inscription** (`/inscription`) : la personne choisit « accueil » ou « atelier ». Le compte reste **en attente** jusqu'à ce que le patron l'accepte dans *Utilisateurs → Demandes d'accès*, en confirmant le rôle.
- **Connexion avec Google** : si l'adresse Google correspond à un compte existant, elle y est rattachée ; sinon, une demande d'accès est créée (même validation par le patron). Seules les adresses vérifiées par Google sont acceptées.

### Activer le bouton Google
1. [console.cloud.google.com](https://console.cloud.google.com) → créer un projet → *API et services* → *Écran de consentement OAuth* (type **Externe**, renseigner le nom de l'application et l'e-mail d'assistance).
2. *Identifiants* → *Créer des identifiants* → *ID client OAuth* → type **Application Web**.
3. *URI de redirection autorisés* : `https://<votre-domaine>/auth/google/callback` (et `http://127.0.0.1:8000/auth/google/callback` pour le local).
4. Copier l'ID client et le code secret dans les variables `GOOGLE_CLIENT_ID` et `GOOGLE_CLIENT_SECRET` (`.env` en local, variables d'environnement chez l'hébergeur).

Sans ces deux variables, le bouton Google n'apparaît pas et seule l'inscription par formulaire est proposée.

## PWA Mécanicien
Accessible via `http://IP:8000/mecanicien` — installable sur Android/iOS depuis Chrome.
