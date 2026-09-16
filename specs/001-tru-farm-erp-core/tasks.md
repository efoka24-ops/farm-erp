---

description: "Task list for TRU FARM ERP core platform implementation"
---

# Tasks: TRU FARM ERP — Plateforme Core

**Input**: Design documents from `specs/001-tru-farm-erp-core/` (spec.md, plan.md)

**Tests**: Inclus (constitution IV/OHADA et principe offline exigent des tests de non-régression sur
calculs financiers et cycles offline).

**Organization**: Tâches groupées par user story (US1–US10, cf. spec.md) pour livraison
incrémentale indépendante.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Peut être exécutée en parallèle (fichiers différents, pas de dépendance)
- **[Story]**: Story rattachée (US1–US10)

## Phase 1: Setup (Infrastructure partagée)

- [x] T001 Créer la structure de dépôt (`api/`, `ia-planificateur/`, `mobile/`, `web/`) selon plan.md
- [x] T002 Initialiser le projet API dans `api/` — **ADAPTÉ** : Laravel 11 (PHP/MySQL) au lieu de
      NestJS/PostgreSQL, l'hébergement cible (Camoo, mutualisé FTP+MySQL) ne supporte pas Node.js
      ni PostgreSQL. Voir README.md.
- [x] T003 [P] Initialiser le projet Flutter dans `mobile/` (Drift/SQLite à intégrer en Phase 2)
- [x] T004 [P] Initialiser le projet React dans `web/` (Vite ; Recharts/React-PDF/MUI à ajouter
      selon besoins des écrans)
- [x] T005 [P] Initialiser le microservice `ia-planificateur/` (squelette FastAPI + requirements
      scikit-learn — hébergement séparé requis, Camoo ne fait pas tourner de process Python long)
- [x] T006 [P] Lint/format : Laravel Pint (api), oxlint (web), dart format par défaut (mobile),
      ruff/black à ajouter avec le code IA (Phase suivante)
- [x] T007 Provisionner MySQL 8.0 (Camoo, remplace PostgreSQL/TimescaleDB) — **ADAPTÉ** : pas de
      Redis sur hébergement mutualisé, cache/queue en driver `file`/`sync` par défaut Laravel

---

## Phase 2: Foundational (Prérequis bloquants)

**⚠️ CRITIQUE**: Aucune user story ne démarre avant la fin de cette phase.

- [x] T008 Schéma de base MySQL (Exploitation, Utilisateur, Animal) + migrations Laravel —
      `database/migrations/`, modèles `app/Models/{Exploitation,User,Animal}.php`
- [x] T009 Row Level Security — **ADAPTÉ** : MySQL/mutualisé n'a pas de RLS native ; implémenté en
      scope applicatif (`app/Models/Scopes/ExploitationScope.php` + trait
      `BelongsToExploitation`), testé avec 2 exploitations isolées
- [x] T010 [P] Authentification : PIN 4 chiffres (bcrypt, verrouillage 5 tentatives) + email/mdp +
      OTP 2FA email (`app/Http/Controllers/Auth/AuthController.php`) — testé de bout en bout
- [x] T011 [P] RBAC par rôle (gérant, agent terrain, comptable, gestionnaire coopérative,
      vétérinaire externe) — `app/Models/Role.php`, middleware `role:` (`EnsureUserHasRole`)
- [x] T012 Journal d'audit append-only — `audit_logs` + trait `Auditable` (auto create/update/delete)
- [x] T013 [P] Moteur de synchronisation offline : file d'actions (`sync_actions`, idempotence par
      UUID client) + delta sync (`SyncController::push/pull`) — testé (push, re-push idempotent, pull)
- [x] T014 [P] Arbitrage manuel des conflits financiers : détection (`sync_conflicts`), notification
      email au gérant, endpoint de résolution réservé au rôle `gerant`
- [x] T015 Configuration environnement — **ADAPTÉ** : pas d'AWS/S3 sur Camoo ; sauvegarde locale
      chiffrée AES-256 (`artisan sauvegarde:executer`) à planifier via cron hébergeur
- [x] T016 [P] Logging structuré JSON (`app/Logging/JsonFormatterTap.php`, canal `structured`) +
      corrélation par requête (`AssignRequestId` middleware, header `X-Request-Id`)

**Checkpoint**: Fondations prêtes — les user stories peuvent démarrer.

---

## Phase 3: User Story 1 - Suivi quotidien du cheptel hors ligne (P1) 🎯 MVP

**Goal**: Agent terrain saisit/consulte animaux, pesées, alimentation, incidents sans connexion.

**Independent Test**: Cycle complet offline (créer fiche, pesée, incident, distribution) puis sync.

### Tests

- [x] T017 [P] [US1] Test cycle offline (`CycleOfflineTest`) : création animal → pesée → incident →
      distribution → sync (idempotente) — volet serveur ; le volet mobile (30j réseau coupé réel sur
      device) reste à valider en UAT terrain (T097)
- [x] T018 [P] [US1] Test contrat API `POST /animaux`, `GET /animaux/:id` (`tests/Contract/AnimalApiTest.php`,
      5 cas dont isolation RLS et recherche par ID TRU TRACE)
- [x] T019 [P] [US1] Test arbitrage conflit financier (`SyncConflitFinancierTest`) — détection +
      résolution réservée au gérant

### Implementation

- [x] T020 [P] [US1] Modèle Animal — Laravel/MySQL (`app/Models/Animal.php`) + miroir offline Drift/SQLite
      mobile (`mobile/lib/core/db/app_database.dart`), ID TRU TRACE généré à la création (`TRU-xxxxxxxxxx`)
- [x] T021 [P] [US1] Écran fiche animal mobile offline (`FicheAnimalScreen`) : liste + création +
      indicateur de sync ; photo/historique détaillé en suivi (US1 reste utilisable sans)
- [x] T022 [US1] Module cheptel API (`AnimalController`, `PeseeController`) : CRUD animal, pesées ;
      acquisitions = création, sorties = changement de statut (vendu/mort/réformé)
- [x] T023 [US1] Module alimentation API (`AlimentationController`) : distributions enregistrées ;
      déduction de stock réelle différée à US9 (Phase 8, module stocks pas encore livré)
- [x] T024 [US1] Écran mobile "Signaler un animal malade" (`SignalerMaladeScreen`) : un seul bouton,
      écrit directement dans la file de sync locale, aucune connexion requise
- [x] T025 [US1] QR TRU TRACE : génération côté mobile (`qr_flutter`, `FicheAnimalScreen`) et endpoint
      API `GET /animaux/{id}/qr` — **scan caméra en suivi** (permissions par plateforme non configurées)
- [x] T026 [US1] Dashboard exécutif : `DashboardController` (API, chiffres + 3 alertes triées par
      gravité) et `DashboardScreen` (mobile, chiffres locaux calculés hors ligne)

**Checkpoint**: US1 fonctionnelle et testable indépendamment (MVP terrain).

---

## Phase 4: User Story 2 - Comptabilité OHADA automatisée (P1)

**Goal**: Dépenses/recettes simplifiées → états financiers SYSCOHADA générés automatiquement.

**Independent Test**: Saisir un mois de mouvements, générer compte de résultat + bilan, valider avec
un expert-comptable OHADA.

### Tests

- [x] T027 [P] [US2] Tests unitaires calculs OHADA — **ADAPTÉ** : PHPUnit (pas de Jest, backend
      Laravel) — `EtatsFinanciersServiceTest` (résultat net, bilan, trésorerie) et
      `CoutRevientServiceTest` (coût de revient, seuil de rentabilité), 9 cas
- [x] T028 [P] [US2] Test intégration flux dépense/recette → état financier (`FluxOhadaTest`,
      via l'API) — 3 cas dont le paiement MTN MoMo non configuré

### Implementation

- [x] T029 [P] [US2] Plan comptable OHADA simplifié (5 classes retenues : 2 immobilisations,
      3 stocks, 5 trésorerie, 6 charges, 7 produits) — migration `comptes_ohada`, 15 comptes seedés
- [x] T030 [US2] Saisie dépense/recette simplifiée (`DepenseController`, `RecetteController`) — choix
      d'un compte OHADA dans une liste courte, lien optionnel vers un animal (coût de revient)
- [x] T031 [US2] Génération compte de résultat, bilan simplifié, tableau de trésorerie
      (`EtatsFinanciersService`) + export PDF — **ADAPTÉ** : dompdf au lieu de PDFKit/Puppeteer
      (Puppeteer nécessite Chromium headless, indisponible sur hébergement mutualisé)
- [x] T032 [US2] Coût de revient par animal + seuil de rentabilité (`CoutRevientService`) — somme des
      dépenses liées à l'animal ; l'alimentation par lot sera répartie automatiquement avec US9
- [x] T033 [US2] Intégration paiement fournisseurs MTN MoMo Rwanda (`MtnMomoService`) — structure
      fonctionnelle contre l'API Collections/Disbursements, **non activée** (aucune credential MTN
      fournie) ; échoue explicitement (422) tant que `MTN_MOMO_*` n'est pas renseigné
- [x] T034 [US2] Écran web comptable (`web/src/features/comptabilite/`) : saisie dépense/recette,
      résumé du mois, export PDF (compte de résultat, bilan) et CSV mouvements — **ADAPTÉ** : CSV
      plutôt que .xlsx natif (évite la dépendance PhpSpreadsheet pour un besoin déjà couvert)
- [ ] T035 [US2] Revue de conformité par expert-comptable agréé OHADA (gate de sortie) — **hors
      périmètre agent** : nécessite un expert-comptable humain agréé OHADA, à planifier côté métier

**Checkpoint**: US1 + US2 fonctionnelles indépendamment.

---

## Phase 5: User Story 3 - Dossier de financement auto certifié (P1)

**Goal**: Génération 1-clic d'un dossier de financement PDF signé, accepté BPR Rwanda/FIDA.

**Independent Test**: 3 exercices complets → PDF généré < 60s avec signature et horodatage
vérifiables.

**Dépend de**: US2 (données comptables), US1 (registre cheptel), intégrations MokineVeto/TRU TRACE.

### Tests

- [ ] T036 [P] [US3] Test intégration génération dossier < 60s (jeu de données 200 membres)
- [ ] T037 [P] [US3] Test vérification signature SHA-256 + horodatage RFC 3161
- [ ] T038 [P] [US3] Test blocage si données 3 exercices incomplètes (liste précise des manques)

### Implementation

- [ ] T039 [P] [US3] Module `api/src/modules/financement/` (vérification complétude, compilation)
- [ ] T040 [US3] Génération PDF multi-sections (comptes OHADA 3 ans, registre cheptel TRU TRACE,
      bilan sanitaire MokineVeto, plan d'investissement, attestation TRU GROUP)
- [ ] T041 [US3] Signature numérique SHA-256 + horodatage RFC 3161
- [ ] T042 [US3] Écran web/mobile "Dossier de financement" (saisie montant/durée/objet, téléchargement)
- [ ] T043 [US3] Calcul plan de remboursement (mensualités selon taux BPR Rwanda)

**Checkpoint**: US1 + US2 + US3 fonctionnelles indépendamment — MVP financement livrable.

---

## Phase 6: User Story 4 - Suivi sanitaire & intégration MokineVeto (P2)

**Goal**: Carnet vaccinal, alertes, import auto des consultations MokineVeto dans le DMA.

**Independent Test**: Consultation créée côté MokineVeto apparaît automatiquement dans le DMA local.

### Tests

- [ ] T044 [P] [US4] Test intégration import automatique MokineVeto → DMA
- [ ] T045 [P] [US4] Test blocage vente animal sous délai d'attente traitement

### Implementation

- [ ] T046 [P] [US4] Module `api/src/modules/sante-veterinaire/` (carnet vaccinal, DMA, traitements)
- [ ] T047 [US4] Webhook/consommateur API MokineVeto (mode dégradé : file si indisponible)
- [ ] T048 [US4] Calendrier vaccinal automatique par espèce/région (protocoles OIE Rwanda)
- [ ] T049 [US4] Alertes J-14/J-3 vaccination (locales offline + push online)
- [ ] T050 [US4] Campagnes de vaccination groupées (saisie en lot)

**Checkpoint**: US4 intégrable indépendamment, alimente US3 (bilan sanitaire).

---

## Phase 7: User Story 8 - Reproduction et naissances (P2)

**Goal**: Calcul auto date de mise bas, alertes, création auto fiches nouveau-nés.

**Independent Test**: Saillie bovine → date +283j calculée, alertes J-14/J-7, mise bas → fiches
créées avec filiation et ID TRU TRACE.

### Tests

- [ ] T051 [P] [US8] Tests unitaires calcul gestation par espèce (bovins/caprins/ovins/porcins/camelins)
- [ ] T052 [P] [US8] Test création automatique fiches nouveau-nés à la mise bas

### Implementation

- [ ] T053 [P] [US8] Module `api/src/modules/reproduction/` (saillies, mises bas, tableau reproduction)
- [ ] T054 [US8] Alertes J-14/J-7 avant mise bas (push berger responsable)
- [ ] T055 [US8] Création auto fiches nouveau-nés (filiation + ID TRU TRACE) — dépend de US1 (T020)

**Checkpoint**: US8 fonctionnelle, enrichit le cheptel de US1.

---

## Phase 8: User Story 9 - Stocks & réapprovisionnement (P2)

**Goal**: Suivi stocks, seuils d'alerte, péremption, valorisation FIFO/PMP.

**Independent Test**: Sorties jusqu'au seuil → alerte ; lot médicament à J-30 péremption → alerte.

### Tests

- [ ] T056 [P] [US9] Test alerte seuil de commande
- [ ] T057 [P] [US9] Test alerte péremption médicament < 30 jours

### Implementation

- [ ] T058 [P] [US9] Module `api/src/modules/stocks/` (catégories, lots, valorisation FIFO/PMP)
- [ ] T059 [US9] Déduction auto stock (liens US1 alimentation, US4 traitements)
- [ ] T060 [US9] Bon de commande PDF auto-généré fournisseur

**Checkpoint**: US9 fonctionnelle, condition la fiabilité du coût alimentaire (US2).

---

## Phase 9: User Story 10 - Ventes & traçabilité TRU TRACE (P2)

**Goal**: Vente enregistrée → bon PDF + certificat TRU TRACE auto lié à l'UUID animal.

**Independent Test**: Vente complète enregistrée → bon PDF généré, certificat TRU TRACE transmis à
l'acheteur.

### Tests

- [ ] T061 [P] [US10] Test intégration génération certificat TRU TRACE à la vente

### Implementation

- [ ] T062 [P] [US10] Module `api/src/modules/ventes/` (vente, carnet clients, paiements différés)
- [ ] T063 [US10] Bon de vente PDF/WhatsApp + facturation TVA optionnelle
- [ ] T064 [US10] Intégration API TRU TRACE (certificat de traçabilité + certificat OIE acheteur)

**Checkpoint**: US10 fonctionnelle, alimente US2 (comptabilité) et US3 (dossier financement).

---

## Phase 10: User Story 5 - Planificateur de ventes intelligent (P2)

**Goal**: Recommandation IA des animaux à vendre selon croissance, coûts et prix RAB.

**Independent Test**: Cheptel simulé → liste priorisée exclut gestants/traitement/reproducteurs,
calcule la marge journalière correctement, en < 5s.

**Dépend de**: US1 (poids/coûts), US9 (coûts stocks), intégration API RAB Rwanda.

### Tests

- [ ] T065 [P] [US5] Tests unitaires calcul marge journalière (pytest)
- [ ] T066 [P] [US5] Test exclusion animaux gestants/traitement/reproducteurs désignés
- [ ] T067 [P] [US5] Test performance recommandation < 5s

### Implementation

- [ ] T068 [P] [US5] Ingestion hebdomadaire prix marché RAB (vendredi 18h) + fallback dernier prix
      connu avec date affichée dans `ia-planificateur/src/data/`
- [ ] T069 [US5] Modèle Random Forest fine-tuné (données RAB 3 ans + ILRI) dans
      `ia-planificateur/src/model/`
- [ ] T070 [US5] Endpoint API IA `/recommandations-ventes` (score, prix estimé, CA potentiel)
- [ ] T071 [US5] Module `api/src/modules/planificateur-ventes/` (client microservice, contraintes
      métier : gestation, traitement, quota sécurité)
- [ ] T072 [US5] Écran "Vendre maintenant" (mobile+web) + bouton "Créer la vente maintenant" → US10
- [ ] T073 [US5] Alerte push hebdomadaire recommandations
- [ ] T074 [US5] Validation concordance > 70% vs panel 5 éleveurs experts (100 cas historiques)

**Checkpoint**: US5 fonctionnelle indépendamment, s'intègre à US10.

---

## Phase 11: User Story 6 - Mode coopérative Girinka (P2)

**Goal**: Tableau de bord consolidé jusqu'à 200 membres + consultation individuelle consentie.

**Independent Test**: Coopérative 200 membres → rapport agrégé < 10s ; accès individuel refusé sans
consentement.

**Dépend de**: T009 (RLS), US1/US2 (données individuelles des membres).

### Tests

- [ ] T075 [P] [US6] Test génération rapport agrégé 200 membres < 10s
- [ ] T076 [P] [US6] Test refus d'accès individuel sans consentement (RLS)
- [ ] T077 [P] [US6] Test alerte solidarité (incident grave signalé)

### Implementation

- [ ] T078 [P] [US6] Module `api/src/modules/cooperative/` (compte coopérative, jusqu'à 200 membres)
- [ ] T079 [US6] Mécanisme de consentement explicite par membre (accès gestionnaire)
- [ ] T080 [US6] Vue matérialisée agrégats coopérative + cache Redis (recalcul 6h)
- [ ] T081 [US6] Rapport d'impact coopérative (format FAO/FIDA/SNV/CARE)
- [ ] T082 [US6] Commandes groupées d'intrants + campagnes de vaccination groupées (lien US9, US4)
- [ ] T083 [US6] Dossier de financement coopératif consolidé (extension US3)
- [ ] T084 [US6] Alerte solidarité (épizootie/mortalité massive) aux membres voisins

**Checkpoint**: US6 fonctionnelle indépendamment.

---

## Phase 12: User Story 7 - Éligibilité automatique MINAGRI (P3)

**Goal**: Détection auto d'éligibilité aux 7 programmes MINAGRI + formulaire pré-rempli.

**Independent Test**: 20 profils contrastés → éligibilité correcte par programme, validée par un
agent MINAGRI.

**Dépend de**: US1, US2, US6 (données cheptel, comptables et coopérative consolidées).

### Tests

- [ ] T085 [P] [US7] Tests unitaires moteur de règles (7 programmes MINAGRI, 20 profils)
- [ ] T086 [P] [US7] Test relance automatique du moteur à mise à jour significative

### Implementation

- [ ] T087 [P] [US7] Module `api/src/modules/eligibilite-minagri/` (règles des 7 programmes)
- [ ] T088 [US7] Déclenchement auto du moteur de règles sur événements (nouvel animal, pesée, membre)
- [ ] T089 [US7] Notification push "nouvelle éligibilité détectée"
- [ ] T090 [US7] Génération formulaire PDF pré-rempli + pièces justificatives TRU TRACE
- [ ] T091 [US7] Suivi statut demande (attente/approuvée/rejetée)

**Checkpoint**: Toutes les user stories sont fonctionnelles indépendamment.

---

## Phase 13: Polish & Cross-Cutting Concerns

- [ ] T092 [P] Rapports & tableaux de bord analytiques transverses (module `rapports/`, §4.15)
- [ ] T093 [P] RH : registre personnel, présences offline, paie, bulletin PDF (module `rh/`, §4.9)
- [ ] T094 [P] Administration & paramétrage (rôles, alertes, races locales, sauvegarde S3, clôture
      exercice OHADA, §4.17)
- [ ] T095 Durcissement sécurité (TLS 1.3, AES-256, revue RLS coopérative)
- [ ] T096 Tests de charge coopérative 50 membres (saisies simultanées)
- [ ] T097 UAT terrain Rwanda (5 éleveurs + 2 coopératives, 8 semaines)
- [ ] T098 Documentation quickstart développeur (`specs/001-tru-farm-erp-core/quickstart.md`)
- [ ] T099 Optimisation performance (APK < 40 Mo, stockage local < 150 Mo, écran d'accueil < 2s/3G)

---

## Dependencies & Execution Order

### Phase Dependencies

- Setup (Phase 1) : aucune dépendance.
- Foundational (Phase 2) : dépend du Setup — bloque toutes les user stories.
- US1, US2 (P1) : démarrables dès la Phase 2 terminée, en parallèle si effectifs suffisants.
- US3 (P1) : dépend de US1 (cheptel) et US2 (comptes) pour compilation du dossier.
- US4, US8, US9, US10 (P2) : démarrables après Phase 2 ; US10 alimente US3/US2, US9 alimente US2.
- US5 (P2) : dépend de US1 (poids/coûts) et US9 (coûts stocks) + API RAB externe.
- US6 (P2) : dépend de la RLS fondationnelle (T009) et des données individuelles US1/US2.
- US7 (P3) : dépend de US1, US2 et US6 (données consolidées coopérative).
- Polish (Phase 13) : après les user stories désirées pour la release.

### Parallel Opportunities

- Toutes les tâches [P] d'une même phase sont sur des fichiers différents et indépendantes.
- Une fois la Phase 2 terminée, US1 et US2 peuvent démarrer en parallèle (toutes deux P1).
- US4, US8, US9, US10 peuvent être menées en parallèle par des développeurs différents une fois
  Phase 2 terminée, en respectant leurs dépendances internes vers US1.

---

## Implementation Strategy

### MVP First
1. Phase 1 (Setup) → Phase 2 (Foundational) → Phase 3 (US1) → **valider** → Phase 4 (US2) →
   Phase 5 (US3) = MVP "cheptel offline + comptabilité OHADA + dossier de financement".

### Incremental Delivery
2. Ajouter US4, US8, US9, US10 (P2 opérationnel) → valider chacune indépendamment.
3. Ajouter US5 (planificateur IA) et US6 (coopérative Girinka) → valider indépendamment.
4. Ajouter US7 (éligibilité MINAGRI) → valider avec agent MINAGRI partenaire.
5. Phase 13 (Polish) → durcissement, RH, admin, UAT terrain Rwanda.
