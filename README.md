# TRU FARM ERP

Plateforme core de gestion d'exploitation agricole (cheptel, comptabilité OHADA, financement,
santé, reproduction, stocks, ventes, planificateur IA, coopérative Girinka).

Spécification complète : [`specs/001-tru-farm-erp-core/`](specs/001-tru-farm-erp-core/).

## Structure du dépôt

- `api/` — API Laravel (PHP/MySQL), déployée par FTP sur l'hébergement mutualisé.
- `web/` — Interface web React (Vite), build statique déployé par FTP.
- `mobile/` — Application terrain Flutter (offline-first, SQLite/Drift).
- `ia-planificateur/` — Microservice FastAPI (recommandations de vente, US5).

## Hébergement / Déploiement

- Hébergement mutualisé Camoo (FTP + MySQL 8.0), domaine `farm-erp.trugroup.cm`.
- Répertoire distant : `/home/trugro9159/farm-erp`.
- La base MySQL n'est accessible qu'en local sur le serveur (`DB_HOST=localhost`) — pas
  d'accès MySQL distant depuis les postes de développement. Le développement local de `api/`
  utilise SQLite (`api/.env`), la configuration MySQL réelle est dans `api/.env.production`
  (non versionné) et n'est utilisée qu'au déploiement.

### Déployer `api/` (Laravel)

1. `composer install --no-dev --optimize-autoloader`
2. Envoyer par FTP le contenu de `api/` (hors `.env`, `vendor/` si géré côté serveur) vers
   `/home/trugro9159/farm-erp/api`.
3. Déposer `.env.production` en tant que `.env` sur le serveur.
4. Exécuter sur le serveur (SSH ou tâche cron/artisan si dispo) : `php artisan migrate --force`.

### Déployer `web/` (React)

1. `cd web && npm run build`
2. Envoyer le contenu de `web/dist/` par FTP vers le sous-répertoire servi par le domaine.

## Développement local

```bash
# API
cd api && composer install && cp .env.example .env && php artisan key:generate && php artisan migrate

# Web
cd web && npm install && npm run dev

# IA Planificateur
cd ia-planificateur && pip install -r requirements.txt && uvicorn src.main:app --reload

# Mobile
cd mobile && flutter pub get && flutter run
```
