# Gestion-garage
🔧 Application web de gestion de garage automobile — clients, réparations, facturation et stock en un seul endroit.

## Stack technique
- **Backend** : Laravel 11
- **Frontend Web** : Blade + CSS custom (thème sombre)
- **PWA Mécanicien** : HTML / CSS / JS pur
- **Base de données** : MySQL (XAMPP)
- **Auth API** : Laravel Sanctum

## Fonctionnalités
- Gestion des clients et véhicules
- Ordres de réparation avec assignation aux mécaniciens
- Planning global de l'atelier
- Devis et facturation PDF
- Tableau de bord patron avec statistiques
- PWA mécanicien accessible via navigateur et installable sur téléphone

## Comptes de démonstration
| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Patron | patron@garage.local | password |
| Accueil | accueil@garage.local | password |
| Mécanicien | kofi@garage.local | password |

## Installation
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## PWA Mécanicien
Accessible via `http://IP:8000/mecanicien` — installable sur Android/iOS depuis Chrome.
