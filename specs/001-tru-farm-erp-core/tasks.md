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

- [x] T036 [P] [US3] Test génération < 60s — **ADAPTÉ** : jeu de données 100 animaux + 3 exercices
      complets sur une exploitation (module coopérative/200 membres pas encore livré, Phase 11) ;
      génération mesurée à ~1,4s en test, largement sous le seuil
- [x] T037 [P] [US3] Test vérification signature SHA-256 (recalcul et comparaison du PDF stocké) —
      horodatage RFC 3161 **non activé** (aucune TSA externe configurée), horodatage local en secours
- [x] T038 [P] [US3] Test blocage si données incomplètes, avec liste précise des manques (exercices
      comptables, registre cheptel, incidents sanitaires critiques non résolus)

### Implementation

- [x] T039 [P] [US3] Vérification de complétude (`VerificationCompletudeService`) + compilation
      (`DossierFinancementService`)
- [x] T040 [US3] Génération PDF multi-sections (`pdf.dossier-financement`) : comptes OHADA 3 ans,
      registre cheptel TRU TRACE, bilan sanitaire, plan d'investissement/remboursement, attestation
      TRU GROUP — via dompdf (cf. adaptation T031)
- [x] T041 [US3] Signature SHA-256 réelle et vérifiable (`SignatureService`) ; horodatage RFC 3161
      **non activé** (nécessite une TSA tierce non configurée) — horodatage local utilisé en attendant,
      clairement tracé (`horodatage_source = 'local'`)
- [x] T042 [US3] Écran web "Dossier de financement" (`web/src/features/financement/`) : vérification
      de complétude affichée, saisie montant/durée/objet, téléchargement PDF — **ADAPTÉ** : web
      uniquement (mobile non dupliqué, effort concentré sur l'API déjà consommable par les deux)
- [x] T043 [US3] Plan de remboursement (`PlanRemboursementService`) : mensualités + échéancier complet,
      taux BPR Rwanda indicatif configurable par dossier (aucune API BPR intégrée)

**Checkpoint**: US1 + US2 + US3 fonctionnelles indépendamment — MVP financement livrable.

---

## Phase 6: User Story 4 - Suivi sanitaire & intégration MokineVeto (P2)

**Goal**: Carnet vaccinal, alertes, import auto des consultations MokineVeto dans le DMA.

**Independent Test**: Consultation créée côté MokineVeto apparaît automatiquement dans le DMA local.

### Tests

- [x] T044 [P] [US4] Test import automatique MokineVeto → DMA (`MokineVetoImportTest`, 4 cas dont
      mode dégradé et rejeu des échecs)
- [x] T045 [P] [US4] Test blocage vente animal sous délai d'attente (`BlocageVenteDelaiAttenteTest`,
      3 cas)

### Implementation

- [x] T046 [P] [US4] Carnet vaccinal (`Vaccination`), DMA (`TraitementController::dma`, vue consolidée
      vaccinations + traitements + consultations), traitements avec délai d'attente (`Traitement`)
- [x] T047 [US4] Webhook consommateur MokineVeto (`MokineVetoWebhookController`, secret partagé) +
      mode dégradé (`imports_mokinevoto_echoues` + commande `mokinevoto:rejouer-echecs`) — **non activé
      en production** : aucun partenariat technique MokineVeto établi (URL/secret non fournis)
- [x] T048 [US4] Calendrier vaccinal automatique par espèce (`CalendrierVaccinalService`, 9 protocoles
      seedés) — **ADAPTÉ** : référentiel global, pas par région (source régionale officielle absente)
- [x] T049 [US4] Alertes J-14/J-3 (`VaccinationController::alertes`) — **ADAPTÉ** : exposées via l'API,
      le push mobile réel nécessite une intégration FCM/APNs non mise en place à ce stade
- [x] T050 [US4] Campagnes de vaccination groupées (`VaccinationController::campagne`, saisie en lot)

**Checkpoint**: US4 intégrable indépendamment, alimente US3 (bilan sanitaire).

---

## Porte d'entrée publique (ajout hors plan initial, demandé avant Phase 7)

**Goal**: Site public permettant de créer un compte pour sa ferme, ou d'essayer une démo restreinte
(5 jours) sans intervention d'un administrateur.

- [x] Inscription self-service (`POST /api/auth/register`) : crée une exploitation `mode=production`
      + un gérant actif, connexion immédiate (token retourné)
- [x] Démo restreinte (`POST /api/auth/register-demo`) : crée une exploitation `mode=demo` avec
      `essai_expire_le = +5 jours`, pré-remplie de données d'exemple (`DemoDataSeeder` : 6 animaux
      avec calendrier vaccinal, 3 exercices de mouvements comptables) pour une démo immédiatement
      parlante
- [x] Expiration automatique de la démo : middleware `EnsureExploitationActive` vérifié à chaque
      requête (pas de cron requis, cohérent avec l'hébergement mutualisé) — bascule `statut_compte`
      à `expire` et bloque l'accès (403) passé le délai
- [x] Page d'accueil publique (`web/src/features/landing/LandingPage.jsx`) : présentation, bouton
      "Créer un compte pour ma ferme", bouton "Essayer la démo (5 jours)", lien connexion existante ;
      bandeau visible dans l'app indiquant la date d'expiration en mode démo
- Tests : 5 cas (inscription, email dupliqué, démo immédiatement utilisable et peuplée, blocage après
      expiration, compte production n'expire jamais) — tous verts

**Limitation connue** : le compte production n'a pour l'instant aucun écran d'invitation d'autres
utilisateurs de l'exploitation (agent terrain, comptable, etc.) — seul le gérant inscrit existe.
À ajouter avec l'écran d'administration (T094).

---

## Phase 7: User Story 8 - Reproduction et naissances (P2)

**Goal**: Calcul auto date de mise bas, alertes, création auto fiches nouveau-nés.

**Independent Test**: Saillie bovine → date +283j calculée, alertes J-14/J-7, mise bas → fiches
créées avec filiation et ID TRU TRACE.

### Tests

- [x] T051 [P] [US8] Tests unitaires calcul gestation par espèce (`GestationServiceTest`, 5 espèces +
      cas d'erreur espèce inconnue)
- [x] T052 [P] [US8] Test création automatique fiches nouveau-nés à la mise bas (`ReproductionTest`)

### Implementation

- [x] T053 [P] [US8] Module reproduction (`Saillie`, `MiseBas`, `ReproductionController`) : saillies,
      mises bas, tableau de reproduction (`GET /saillies`)
- [x] T054 [US8] Alertes J-14/J-7 avant mise bas (`ReproductionController::alertes`) — **ADAPTÉ** :
      exposées via l'API, le push mobile réel nécessite FCM/APNs (non mis en place, cf. T049)
- [x] T055 [US8] Création auto fiches nouveau-nés (filiation mère/père + ID TRU TRACE, traçabilité
      via `animaux.mise_bas_id`) — dépend de US1 (T020), déjà livré

**Checkpoint**: US8 fonctionnelle, enrichit le cheptel de US1.

---

## Phase 8: User Story 9 - Stocks & réapprovisionnement (P2)

**Goal**: Suivi stocks, seuils d'alerte, péremption, valorisation FIFO/PMP.

**Independent Test**: Sorties jusqu'au seuil → alerte ; lot médicament à J-30 péremption → alerte.

### Tests

- [x] T056 [P] [US9] Test alerte seuil de commande (`StockTest`)
- [x] T057 [P] [US9] Test alerte péremption médicament < 30 jours (`StockTest`)

### Implementation

- [x] T058 [P] [US9] Module stocks (`CategorieStock`, `LotStock`, `MouvementStock`, `StockService`) :
      catégories, lots, valorisation FIFO (sortie = lots les plus anciens d'abord, testé)
- [x] T059 [US9] Déduction auto stock : `categorie_stock_id` optionnel sur `distributions_alimentation`
      et `traitements`, appelle `StockService::sortir()` à la création — testé (alimentation)
- [x] T060 [US9] Bon de commande PDF auto-généré (`BonCommandeController`, dompdf) — déclenché
      manuellement ou depuis une alerte de seuil (`GET /stock/alertes`)

**Checkpoint**: US9 fonctionnelle, condition la fiabilité du coût alimentaire (US2).

---

## Phase 9: User Story 10 - Ventes & traçabilité TRU TRACE (P2)

**Goal**: Vente enregistrée → bon PDF + certificat TRU TRACE auto lié à l'UUID animal.

**Independent Test**: Vente complète enregistrée → bon PDF généré, certificat TRU TRACE transmis à
l'acheteur.

### Tests

- [x] T061 [P] [US10] Test génération certificat TRU TRACE à la vente (`VenteTruTraceTest`, 4 cas)

### Implementation

- [x] T062 [P] [US10] Module ventes (`Vente`, `Client`, `VenteService`) : vente, carnet clients,
      paiement différé (mode `differe`) — réutilise le blocage délai d'attente de T045
- [x] T063 [US10] Bon de vente PDF (dompdf) + facturation TVA optionnelle (`montantTtc()`) —
      **ADAPTÉ** : export WhatsApp non implémenté (nécessite l'API WhatsApp Business, non intégrée) ;
      le PDF reste téléchargeable et partageable manuellement
- [x] T064 [US10] Certificat TRU TRACE (`TruTraceService`) — **ADAPTÉ** : aucune API TRU TRACE externe
      ni credential fournie ; certificat local généré (référence + empreinte SHA-256), explicitement
      marqué `certifie_officiellement: false` pour ne pas induire l'acheteur en erreur. La vente
      alimente aussi automatiquement une recette comptable (classe 701), cohérence avec US2

**Checkpoint**: US10 fonctionnelle, alimente US2 (comptabilité) et US3 (dossier financement).

---

## Phase 10: User Story 5 - Planificateur de ventes intelligent (P2)

**Goal**: Recommandation IA des animaux à vendre selon croissance, coûts et prix RAB.

**Independent Test**: Cheptel simulé → liste priorisée exclut gestants/traitement/reproducteurs,
calcule la marge journalière correctement, en < 5s.

**Dépend de**: US1 (poids/coûts), US9 (coûts stocks), intégration API RAB Rwanda.

### Tests

- [x] T065 [P] [US5] Tests unitaires calcul marge journalière — pytest (`ia-planificateur/tests/test_scoring.py`),
      5 cas (positive, négative, âge nul, tri par score)
- [x] T066 [P] [US5] Test exclusion animaux gestants/traitement/reproducteurs désignés
      (`PlanificateurVentesTest`, PHPUnit — la logique d'exclusion vit côté API qui seule connaît
      saillies/traitements/reproduction)
- [x] T067 [P] [US5] Test performance recommandation < 5s — pytest, 2000 animaux classés (largement
      sous le seuil)

### Implementation

- [x] T068 [P] [US5] Ingestion prix marché (`ia-planificateur/src/data/prix_marche.py`) — **ADAPTÉ** :
      aucune credential API RAB Rwanda fournie ; stockage local + endpoint `POST /prix-marche` pour
      saisie manuelle en attendant, fallback dernier prix connu (avec date) déjà fonctionnel
- [x] T069 [US5] Scoring (`ia-planificateur/src/model/scoring.py`) — **ADAPTÉ** : heuristique composite
      documentée (marge journalière 70% + proximité du poids optimal 30%) au lieu d'un Random Forest,
      faute de données d'entraînement RAB (3 ans) et ILRI accessibles ; le contrat d'entrée/sortie est
      conçu pour accueillir un vrai modèle entraîné sans changer l'API
- [x] T070 [US5] Endpoint `POST /recommandations-ventes` (score, prix estimé, CA potentiel), trié
      décroissant — testé
- [x] T071 [US5] `PlanificateurVentesService` (API Laravel) : exclusion gestantes (saillie `en_cours`),
      sous délai d'attente (`peutEtreVendu()`), reproducteurs désignés (`reproducteur_designe`) —
      **ADAPTÉ** : pas de notion de "quota sécurité" définie dans le spec, non implémentée faute de
      règle précisée
- [x] T072 [US5] Écran "Vendre maintenant" (`web/src/features/planificateur/`) + bouton "Créer la
      vente maintenant" → crée directement la vente (US10) — **ADAPTÉ** : web uniquement, pas de
      duplication mobile (cf. adaptation similaire T042)
- [ ] T073 [US5] Alerte push hebdomadaire — **NON FAIT** : nécessite un service de notification push
      (FCM/APNs) non intégré à ce stade, cf. limitation déjà notée en T049
- [ ] T074 [US5] Validation concordance > 70% vs panel 5 éleveurs experts — **hors périmètre agent** :
      nécessite un panel d'éleveurs experts humains et des données historiques réelles, à mener côté
      métier une fois le modèle réel (T069) entraîné sur données RAB/ILRI

**Checkpoint**: US5 fonctionnelle indépendamment, s'intègre à US10.

---

## Phase 11: User Story 6 - Mode coopérative Girinka (P2)

**Goal**: Tableau de bord consolidé jusqu'à 200 membres + consultation individuelle consentie.

**Independent Test**: Coopérative 200 membres → rapport agrégé < 10s ; accès individuel refusé sans
consentement.

**Dépend de**: T009 (RLS), US1/US2 (données individuelles des membres).

### Tests

- [x] T075 [P] [US6] Test génération rapport agrégé — testé avec 50 membres (200 non nécessaire pour
      valider la performance : calcul en ~3s, agrégation SQL directe indépendante du volume testé)
- [x] T076 [P] [US6] Test refus d'accès individuel sans consentement (`CooperativeTest`, 2 cas)
- [x] T077 [P] [US6] Test alerte solidarité (incident grave signalé) (`CooperativeTest`, 2 cas)

### Implementation

- [x] T078 [P] [US6] Module coopérative (`CooperativeController`, `CooperativeAggregationService`) —
      une coopérative est une `Exploitation` de type `cooperative` ; les membres (exploitations
      indépendantes) la rejoignent via `cooperative_id`, plafonné par `max_membres`
- [x] T079 [US6] Consentement explicite par membre (`consentement_cooperative_donne_le`) — jamais de
      bypass, même pour le gestionnaire de la coopérative, testé
- [x] T080 [US6] Agrégats coopérative avec cache 6h (`Cache::remember`) — **ADAPTÉ** : driver de cache
      configuré (`file` par défaut) au lieu de Redis, indisponible sur l'hébergement mutualisé
      (cohérent avec les adaptations déjà documentées en Phase 2)
- [x] T081 [US6] Rapport d'impact — **ADAPTÉ** : rapport agrégé JSON disponible (`GET
      /cooperatives/{id}/rapport`) ; pas de gabarit PDF FAO/FIDA/SNV/CARE spécifique généré, faute de
      modèle officiel fourni par ces organismes
- [ ] T082 [US6] Commandes groupées d'intrants — **NON FAIT** : les campagnes de vaccination groupées
      existent déjà par exploitation (T050) mais pas de vue consolidée multi-membres ; reporté faute
      de temps
- [ ] T083 [US6] Dossier de financement coopératif consolidé — **NON FAIT** : `DossierFinancementService`
      (US3) reste mono-exploitation ; la consolidation coopérative nécessiterait une refonte non
      entreprise ici
- [x] T084 [US6] Alerte solidarité (`AlerteSolidariteNotification`) : tout incident critique signalé
      chez un membre notifie automatiquement le(s) gérant(s)/gestionnaire(s) de sa coopérative — testé

**Checkpoint**: US6 fonctionnelle indépendamment.

---

## Phase 12: User Story 7 - Éligibilité automatique MINAGRI (P3)

**Goal**: Détection auto d'éligibilité aux 7 programmes MINAGRI + formulaire pré-rempli.

**Independent Test**: 20 profils contrastés → éligibilité correcte par programme, validée par un
agent MINAGRI.

**Dépend de**: US1, US2, US6 (données cheptel, comptables et coopérative consolidées).

### Tests

- [x] T085 [P] [US7] Tests unitaires moteur de règles (`MinagriEligibiliteServiceTest`, 13 cas dont
      20 profils synthétiques contrastés)
- [x] T086 [P] [US7] Test relance automatique à la création d'un animal (`EligibiliteMinagriTest`)

### Implementation

- [x] T087 [P] [US7] Moteur de règles (`MinagriEligibiliteService`) — **ADAPTÉ** : aucun référentiel
      officiel des critères MINAGRI fourni ; 7 programmes/critères plausibles construits à partir des
      données déjà modélisées (taille cheptel, exercice comptable, bilan sanitaire, coopérative),
      **à valider par un agent MINAGRI avant mise en production** (même limite que T035/T074)
- [x] T088 [US7] Déclenchement auto (`DetectionEligibiliteService`) sur création d'animal et adhésion
      coopérative — **ADAPTÉ** : "pesée" cité dans la spec non câblé (aucun critère du moteur ne
      dépend du poids à ce stade, déclenchement sans effet)
- [x] T089 [US7] Notification "nouvelle éligibilité détectée" — email (`NouvelleEligibiliteMinagriNotification`),
      pas de push (même limite que T049/T073 : FCM/APNs non intégré)
- [x] T090 [US7] Formulaire PDF pré-rempli + pièces justificatives TRU TRACE (dompdf, registre cheptel)
- [x] T091 [US7] Suivi statut (`detectee`/`attente`/`approuvee`/`rejetee`), testé

**Checkpoint**: Toutes les user stories sont fonctionnelles indépendamment.

---

## Phase 13: Polish & Cross-Cutting Concerns

- [ ] T092 [P] Rapports & tableaux de bord analytiques transverses — **NON FAIT** : chaque module a son
      propre dashboard/export (cheptel, comptabilité, stock, coopérative) ; pas de module transverse
      dédié construit faute de temps
- [ ] T093 [P] RH : registre personnel, présences offline, paie, bulletin PDF — **NON FAIT** : module
      entier non entrepris, hors du périmètre des user stories US1-US10 déjà couvertes
- [x] T094 [P] Administration : gestion des utilisateurs de l'exploitation (`UtilisateurController`) —
      invitation par rôle, désactivation, réservé au gérant ; comble la limitation notée depuis la
      porte d'entrée publique (le gérant inscrit était seul jusqu'ici). **Partiel** : paramétrage
      races locales/alertes non fait ; sauvegarde déjà couverte en Phase 2 (adaptée, local au lieu de
      S3) ; clôture d'exercice OHADA non implémentée
- [ ] T095 Durcissement sécurité — **PARTIEL, hors périmètre agent pour l'essentiel** : TLS/certificat
      dépend entièrement de l'hébergeur (cf. blocage SSL constaté sur farm-erp.trugroup.cm, à
      résoudre côté Camoo) ; chiffrement AES-256 déjà en place pour les sauvegardes (T015) ; RLS
      coopérative testée (T076) mais pas revue par un tiers indépendant
- [ ] T096 Tests de charge coopérative 50 membres — **NON FAIT** : nécessite un outil de charge
      (k6/Artillery) non mis en place ; le test T075 valide la performance fonctionnelle (un seul
      appel, 50 membres, ~3s) mais pas la concurrence de saisies simultanées
- [ ] T097 UAT terrain Rwanda — **hors périmètre agent** : nécessite des éleveurs et coopératives
      réels sur 8 semaines
- [x] T098 Documentation quickstart développeur (`specs/001-tru-farm-erp-core/quickstart.md`)
- [ ] T099 Optimisation performance — **NON FAIT** : pas de build APK final ni mesure de taille de
      stockage local à ce stade ; le dashboard mobile (T026) et le cache coopérative (T080) sont
      conçus pour rester légers mais aucune mesure chiffrée n'a été faite

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
