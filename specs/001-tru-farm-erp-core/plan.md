# Implementation Plan: TRU FARM ERP — Plateforme Core

**Branch**: `001-tru-farm-erp-core` | **Date**: 2026-09-16 | **Spec**: `specs/001-tru-farm-erp-core/spec.md`

**Input**: Feature specification from `specs/001-tru-farm-erp-core/spec.md`

## Summary

Construire un ERP agropastoral offline-first couvrant cheptel, santé, reproduction, alimentation,
stocks, comptabilité OHADA, RH, ventes/traçabilité, ainsi que 4 modules d'innovation (dossier de
financement auto, planificateur de ventes IA, mode coopérative Girinka, éligibilité MINAGRI).
Approche technique : app mobile Flutter offline-first synchronisée en delta avec un backend NestJS
modulaire, web React pour les rôles gestion/comptabilité, microservice IA dédié pour le
planificateur de ventes, PostgreSQL/TimescaleDB avec Row Level Security pour l'isolation des
coopératives.

## Technical Context

**Language/Version**: Dart (Flutter 3.x) pour mobile, TypeScript (NestJS) pour backend, React.js
(TypeScript) pour web, Python 3.11 (FastAPI) pour le microservice IA.

**Primary Dependencies**: Flutter + Drift ORM (SQLite), NestJS + TypeORM/Prisma, React + Recharts +
React-PDF + MUI, scikit-learn (Random Forest), PDFKit + Puppeteer, MTN MoMo Rwanda API v2, API
MokineVeto, API TRU TRACE, API RAB Rwanda, API MINAGRI Rwanda.

**Storage**: PostgreSQL 16 (données structurées + Row Level Security), TimescaleDB (séries
temporelles : poids, production laitière, prix marché RAB), SQLite local via Drift (mobile
offline), Redis (cache agrégats coopérative, sessions).

**Testing**: Jest + Supertest (NestJS), Flutter Test + simulation réseau coupé (mobile), pytest
(microservice IA), Postman/Newman (contrats API), audit manuel expert-comptable OHADA.

**Target Platform**: Android 8.0+ (mobile offline-first), navigateurs web modernes (backoffice),
AWS af-south-1 (hébergement backend/API/IA).

**Project Type**: Mobile + Web + API (architecture 4 briques : mobile offline-first, web
backoffice, backend API modulaire, microservice IA).

**Performance Goals**: écran d'accueil < 2s sur 3G ; génération dossier financement < 60s (200
membres) ; recommandation IA < 5s ; rapport coopératif agrégé < 10s (200 membres, cache Redis 6h).

**Constraints**: 100% des saisies terrain offline-capable ; APK < 40 Mo ; stockage local < 150 Mo/
exploitation ; uptime ≥ 99,5% ; conformité SYSCOHADA révisé 2017 ; conformité Rwanda DPPA 2021 ;
signature SHA-256 + horodatage RFC 3161 sur les dossiers de financement.

**Scale/Scope**: 10 000 exploitations actives phase 1 ; coopératives jusqu'à 200 membres ; 17
modules fonctionnels + 4 modules d'innovation.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Offline-First** → PASS. Toute story P1/P2 de saisie terrain (US1, US4, US8, US9) est conçue
  mobile-first avec SQLite local et sync delta ; aucune fonctionnalité de saisie terrain ne dépend
  du réseau. Seule la génération de dossier financement (US3, opération ponctuelle web/serveur) et
  les appels API tiers (RAB, MINAGRI) requièrent une connexion, conformément au principe VI
  (dépendances dégradables).
- **II. Conformité OHADA** → PASS conditionnel. Le module comptable (US2, FR-004/FR-005) doit être
  validé par un expert-comptable OHADA avant mise en production (Gate de sortie Phase 1).
- **III. Sécurité & Consentement** → PASS. RLS PostgreSQL pour isolation coopérative (US6,
  FR-013), RBAC par rôle (FR-022), consentement explicite requis avant toute lecture individuelle
  par un gestionnaire de coopérative.
- **IV. Traçabilité & Intégrité Probante** → PASS. Journal d'audit append-only (FR-023), signature
  SHA-256 + horodatage RFC 3161 sur le dossier de financement (FR-006).
- **V. Simplicité d'usage** → PASS. Interfaces mobile terrain limitées aux actions 1-touche
  (US1) ; complexité (IA, comptabilité, financement) encapsulée côté backend.
- **VI. Intégrations dégradables** → PASS. Import MokineVeto en mode file d'attente si API
  indisponible ; dernier prix RAB connu utilisé avec date affichée si l'API RAB échoue (Edge Case
  spec).
- **VII. Architecture modulaire** → PASS. Découpage en modules NestJS indépendants par domaine
  (cheptel, santé, reproduction, alimentation, stocks, comptabilité, RH, ventes, financement, IA
  ventes, coopérative, éligibilité), chacun consommé par mobile et web via API commune.

Aucune violation nécessitant une entrée en Complexity Tracking à ce stade.

## Project Structure

### Documentation (this feature)

```text
specs/001-tru-farm-erp-core/
├── plan.md              # Ce fichier
├── research.md          # Phase 0 (à produire : choix ORM, stratégie sync, fine-tuning RF)
├── data-model.md         # Phase 1 (entités détaillées : Exploitation, Animal, Ecriture, etc.)
├── quickstart.md         # Phase 1 (setup dev local mobile+web+api+ia)
├── contracts/             # Phase 1 (contrats API REST par module)
└── tasks.md               # Phase 2 (/speckit-tasks)
```

### Source Code (repository root)

```text
api/                          # Backend NestJS modulaire
├── src/
│   ├── modules/
│   │   ├── auth/
│   │   ├── cheptel/
│   │   ├── sante-veterinaire/       # intégration MokineVeto
│   │   ├── reproduction/
│   │   ├── alimentation/
│   │   ├── stocks/
│   │   ├── comptabilite-ohada/
│   │   ├── rh/
│   │   ├── ventes/                  # intégration TRU TRACE
│   │   ├── financement/             # innovation 1 : dossier BPR/FIDA
│   │   ├── planificateur-ventes/    # innovation 2 : client du microservice IA
│   │   ├── cooperative/             # innovation 3 : mode Girinka
│   │   ├── eligibilite-minagri/     # innovation 4
│   │   ├── sync/                    # moteur de synchronisation offline
│   │   └── rapports/
│   ├── common/ (rbac, audit-log, rls-guards)
│   └── main.ts
└── tests/
    ├── contract/
    ├── integration/
    └── unit/

ia-planificateur/              # Microservice Python FastAPI + scikit-learn
├── src/
│   ├── model/ (random_forest.py, features.py)
│   ├── api/ (routes.py)
│   └── data/ (ingestion prix RAB, ILRI)
└── tests/

mobile/                        # App Flutter offline-first
├── lib/
│   ├── features/ (cheptel, sante, reproduction, alimentation, stocks, finances, ventes, rh, sync)
│   ├── data/ (drift/ schema SQLite local)
│   └── core/ (auth PIN offline, sync engine, alertes locales)
└── test/

web/                            # Backoffice React
├── src/
│   ├── pages/ (dashboard, comptabilite, financement, cooperative, eligibilite, rapports, admin)
│   ├── components/
│   └── services/ (client API)
└── tests/
```

**Structure Decision**: Architecture à 4 briques (mobile Flutter, web React, API NestJS modulaire,
microservice IA Python) reflétant l'architecture fonctionnelle de la spec (section 3). Chaque
module métier de `api/src/modules/` correspond à un module fonctionnel de la SFD (4.3 à 4.14),
consommé indifféremment par `mobile/` et `web/` via contrats REST versionnés, conformément au
principe constitutionnel VII.

## Complexity Tracking

Aucune violation constitutionnelle identifiée nécessitant justification à ce stade.
