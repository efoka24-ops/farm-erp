# Quickstart développeur — TRU FARM ERP

## Prérequis

- PHP 8.2+ et Composer
- Node.js 18+ et npm
- Python 3.12+ (3.14 non supporté par `pandas`/`scikit-learn` à ce jour — utiliser `uv python install 3.12`)
- Flutter SDK (pour `mobile/`)

## API (Laravel)

```bash
cd api
composer install
cp .env.example .env
php artisan key:generate
# DB_CONNECTION=sqlite par défaut en dev (voir README.md racine pour le détail
# MySQL de production Camoo, inaccessible en remote)
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Identifiants de démo après seed : `gerant@trufarm.test` / `password`.

### Lancer les tests

```bash
php artisan test          # 90+ tests, ~10-120s selon la machine
vendor/bin/pint           # formatage automatique
```

## Web (React)

```bash
cd web
npm install
npm run dev       # http://localhost:5173
npm run build     # dist/ prêt pour déploiement FTP statique
npx oxlint         # lint
```

Variable d'environnement `VITE_API_URL` (voir `.env.production`) pour pointer
vers l'API en production.

## Microservice IA (planificateur de ventes)

```bash
cd ia-planificateur
uv venv --python 3.12
uv pip install -r requirements-dev.txt --python .venv
uv run --python .venv uvicorn src.main:app --reload   # http://localhost:8000
uv run --python .venv pytest -v
```

L'API Laravel appelle ce microservice via `IA_PLANIFICATEUR_URL` (voir
`.env.example`) — non requis pour développer le reste de l'app.

## Mobile (Flutter)

```bash
cd mobile
flutter pub get
dart run build_runner build --force-jit --delete-conflicting-outputs  # génère app_database.g.dart (Drift)
flutter analyze
flutter test
flutter run
```

Note : sur Dart/Flutter récent, `build_runner` peut échouer sans
`--force-jit` (bug de compilation AOT des builders sur certaines versions
SDK) — toujours utiliser ce flag pour la génération Drift.

## Intégrations externes — état

Aucune des intégrations suivantes n'a de credential réelle configurée ;
chacune échoue explicitement (jamais silencieusement) tant que la variable
d'environnement correspondante est vide :

| Intégration | Variable(s) `.env` | État |
|---|---|---|
| MTN MoMo Rwanda (paiement fournisseurs) | `MTN_MOMO_*` | Structure prête, non activée |
| MokineVeto (import consultations vétérinaires) | `MOKINEVOTO_WEBHOOK_SECRET` | Webhook prêt, mode dégradé fonctionnel |
| TRU TRACE (certificat de traçabilité) | `TRU_TRACE_API_URL`, `TRU_TRACE_API_KEY` | Certificat local en fallback |
| RFC 3161 (horodatage dossier de financement) | `RFC3161_TSA_URL` | Horodatage local en fallback |
| RAB Rwanda (prix marché) | — (ia-planificateur) | Saisie manuelle en fallback |
| API BPR Rwanda (taux de crédit) | — | Taux saisi manuellement par dossier |

## Déploiement

Voir [README.md](../../README.md) à la racine du dépôt pour le processus de
déploiement FTP vers l'hébergement mutualisé Camoo (contraintes : pas de
SSH fonctionnel au moment de la rédaction, pas de Redis, pas de cron
garanti — voir les adaptations documentées dans `tasks.md`).

## Où trouver quoi

- `specs/001-tru-farm-erp-core/spec.md` — spécification fonctionnelle complète
- `specs/001-tru-farm-erp-core/plan.md` — plan technique initial
- `specs/001-tru-farm-erp-core/tasks.md` — suivi tâche par tâche, avec toutes
  les adaptations à la stack documentées (mutualisé Camoo au lieu de
  l'infra initialement prévue)
