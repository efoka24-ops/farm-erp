# Feature Specification: TRU FARM ERP — Plateforme Core

**Feature Branch**: `001-tru-farm-erp-core`

**Created**: 2026-09-16

**Status**: Draft

**Input**: User description: "TRU FARM ERP - ERP agropastoral offline-first, comptabilité OHADA,
dossier de financement automatique BPR/FIDA, planificateur de ventes IA, mode coopérative Girinka,
éligibilité MINAGRI. Source: SFD-TFERP-2026-v1.1.md"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Suivi quotidien du cheptel hors ligne (Priority: P1)

Un agent d'élevage/berger, sans connexion internet sur son exploitation, ouvre l'application mobile
pour consulter la fiche d'un animal (scan QR TRU TRACE), enregistrer une distribution alimentaire,
signaler un animal malade ou saisir une pesée. Toutes ces actions doivent être possibles et
immédiatement reflétées localement, puis synchronisées automatiquement à la reconnexion.

**Why this priority**: C'est le cœur d'usage quotidien de l'application et la raison d'être du
positionnement offline-first ; sans cela, rien d'autre n'a de valeur sur le terrain rwandais/
camerounais où la connectivité est intermittente.

**Independent Test**: Couper le réseau, effectuer un cycle complet (créer fiche animal, saisir
pesée, signaler incident, distribuer aliment), reconnecter, vérifier la synchronisation intégrale
sans perte de données.

**Acceptance Scenarios**:

1. **Given** l'application mobile sans connexion réseau, **When** l'agent scanne le QR code d'un
   animal, **Then** la fiche complète (historique, santé, filiation) s'affiche depuis les données
   locales SQLite.
2. **Given** une distribution alimentaire saisie hors ligne, **When** la connexion revient,
   **Then** la donnée est synchronisée au serveur et le stock correspondant est déduit
   automatiquement.
3. **Given** un conflit de données financières entre saisie locale et serveur, **When** la
   synchronisation détecte la divergence, **Then** le gérant est notifié pour arbitrage manuel (pas
   de résolution automatique last-write-wins sur les données financières).

---

### User Story 2 - Comptabilité OHADA automatisée sans expertise comptable (Priority: P1)

Un gérant ou comptable saisit une dépense (alimentation, santé, main-d'œuvre) ou une recette (vente
d'animal, lait, subvention) via un formulaire simplifié, et le système génère automatiquement les
états financiers conformes SYSCOHADA révisé 2017 (compte de résultat, bilan simplifié, tableau de
trésorerie) sans que l'utilisateur ait à connaître le plan comptable.

**Why this priority**: Résout le problème central identifié (99% des exploitations sans registre
structuré) et conditionne l'accès au financement (dépend de P3).

**Independent Test**: Saisir un mois complet de dépenses/recettes, générer le compte de résultat et
le bilan simplifié, faire valider la conformité SYSCOHADA par un expert-comptable.

**Acceptance Scenarios**:

1. **Given** une dépense saisie avec catégorie "alimentation", **When** elle est enregistrée,
   **Then** elle est automatiquement classée en Classe 6 (Charges) et liée au module stocks.
2. **Given** un mois de saisies complètes, **When** le gérant demande le compte de résultat,
   **Then** le document généré respecte le format SYSCOHADA révisé 2017 et affiche le résultat net.
3. **Given** une vente d'animal enregistrée, **When** elle est validée, **Then** la recette est
   automatiquement classée en Classe 7 (Produits) et liée au module ventes.

---

### User Story 3 - Génération automatique du dossier de financement certifié (Priority: P1)

Un gérant, souhaitant obtenir un crédit agricole, ouvre le module "Dossier de financement", saisit
le montant, la durée et l'objet du crédit. Le système compile automatiquement les 3 derniers
exercices comptables, le registre du cheptel certifié TRU TRACE et le bilan sanitaire MokineVeto en
un PDF signé numériquement et horodaté, accepté par la BPR Rwanda et les guichets FIDA/BAD.

**Why this priority**: C'est l'innovation différenciante n°1 — elle lève le principal blocage
d'accès au crédit agricole identifié dans le contexte, et constitue un argument commercial fort.

**Independent Test**: Avec 3 exercices de données complètes, générer le dossier PDF et vérifier
qu'il contient les 7 documents requis, la signature SHA-256 et l'horodatage RFC 3161, en moins de
60 secondes.

**Acceptance Scenarios**:

1. **Given** des données comptables incomplètes sur les 3 derniers exercices, **When** le gérant
   demande la génération, **Then** le système liste précisément les saisies manquantes avant de
   bloquer la génération.
2. **Given** des données complètes, **When** le gérant valide montant/durée/objet, **Then** le
   dossier PDF complet est généré en moins de 60 secondes, signé et horodaté.
3. **Given** un dossier généré, **When** il est ouvert par un tiers (banque), **Then** la signature
   numérique et l'horodatage RFC 3161 sont vérifiables.

---

### User Story 4 - Suivi sanitaire et intégration MokineVeto (Priority: P2)

Un agent ou gérant consulte le carnet de vaccination d'un animal, reçoit des alertes de rappel
(J-14, J-3), et voit automatiquement apparaître dans le DMA de l'animal les consultations réalisées
via MokineVeto, sans ressaisie.

**Why this priority**: Condition la qualité du bilan sanitaire utilisé dans le dossier de
financement (P3) et dans l'éligibilité MINAGRI (P2 innovations), et réduit la mortalité évitable.

**Independent Test**: Créer une consultation dans MokineVeto pour un animal existant, vérifier son
apparition automatique dans le DMA TRU FARM ERP sans action manuelle.

**Acceptance Scenarios**:

1. **Given** un animal avec un vaccin dû dans 14 jours, **When** la date J-14 est atteinte,
   **Then** une notification est envoyée au responsable de l'animal.
2. **Given** une consultation MokineVeto enregistrée pour un animal identifié, **When** elle est
   validée côté MokineVeto, **Then** elle apparaît automatiquement dans le DMA TRU FARM ERP
   correspondant.
3. **Given** un animal sous traitement avec délai d'attente avant abattage non écoulé, **When** un
   utilisateur tente de le mettre en vente, **Then** le système affiche une alerte bloquante.

---

### User Story 5 - Planificateur de ventes intelligent (Priority: P2)

Un gérant ouvre l'écran "Vendre maintenant" et consulte une liste priorisée de 3 à 10 animaux dont
la vente ce mois maximiserait son revenu, calculée à partir de la croissance interne, des coûts
d'exploitation et des prix marché RAB Rwanda actualisés chaque vendredi.

**Why this priority**: Différenciateur majeur vs concurrents (CattleMax/Farmbrite ne font que du
reporting rétrospectif) ; impact direct sur le revenu de l'éleveur, dépend cependant de données de
cheptel et coûts déjà saisies (P1/P2).

**Independent Test**: Avec un cheptel simulé et des prix RAB de référence, vérifier que la
recommandation exclut les animaux gestants/sous traitement/reproducteurs désignés et calcule
correctement la marge journalière.

**Acceptance Scenarios**:

1. **Given** un cheptel avec historique de pesées et coûts, **When** le planificateur est exécuté,
   **Then** il retourne une liste triée par score de priorité avec estimation de prix et CA
   potentiel, en moins de 5 secondes.
2. **Given** un animal gestant ou sous traitement vétérinaire, **When** le planificateur calcule
   les recommandations, **Then** cet animal est exclu de la liste.
3. **Given** une recommandation affichée, **When** le gérant clique "Créer la vente maintenant",
   **Then** le formulaire de vente est pré-rempli avec les données de l'animal recommandé.

---

### User Story 6 - Mode coopérative Girinka multi-exploitations (Priority: P2)

Un gestionnaire de coopérative Girinka consulte un tableau de bord consolidé de ses 50 à 200
membres (cheptel total, CA agrégé, mortalité moyenne) et peut, avec le consentement de chaque
membre, consulter les indicateurs individuels détaillés d'un membre.

**Why this priority**: Ouvre le marché institutionnel (coopératives, bailleurs FAO/FIDA/SNV/CARE)
et différencie l'offre des ERP mono-exploitation existants, mais suppose que le module
exploitation individuelle (P1/P2) soit stable.

**Independent Test**: Simuler une coopérative de 50 membres avec données hétérogènes, générer le
rapport agrégé en moins de 10 secondes, vérifier l'isolation des données (Row Level Security) entre
membres sans consentement croisé.

**Acceptance Scenarios**:

1. **Given** une coopérative de 200 membres avec données à jour, **When** le gestionnaire demande
   le rapport agrégé, **Then** il est généré en moins de 10 secondes (cache Redis, recalcul 6h).
2. **Given** un membre n'ayant pas donné son consentement, **When** le gestionnaire tente de
   consulter le détail de son exploitation, **Then** l'accès est refusé.
3. **Given** un incident sanitaire grave signalé par un membre, **When** il est enregistré,
   **Then** le gestionnaire et les membres géographiquement voisins reçoivent une alerte solidarité.

---

### User Story 7 - Éligibilité automatique aux aides MINAGRI (Priority: P3)

Un gérant ou gestionnaire de coopérative est notifié automatiquement lorsque son exploitation
devient éligible à l'un des 7 programmes d'aide MINAGRI Rwanda (Girinka, vaccins, intrants,
crédit RAB, assurance KLIP), et peut soumettre en un clic un formulaire pré-rempli.

**Why this priority**: Fort effet différenciant institutionnel, mais dépend de données déjà
consolidées (cheptel, comptabilité, coopérative) issues des stories précédentes ; valeur immédiate
moindre que le financement ou le planificateur de ventes.

**Independent Test**: Avec 20 profils d'exploitation contrastés, vérifier que le moteur de règles
identifie correctement l'éligibilité à chaque programme et génère le formulaire pré-rempli
correspondant.

**Acceptance Scenarios**:

1. **Given** une mise à jour significative des données (nouvel animal, nouvelle pesée), **When**
   elle est enregistrée, **Then** le moteur de règles d'éligibilité est relancé automatiquement.
2. **Given** une exploitation nouvellement éligible à un programme, **When** l'éligibilité est
   détectée, **Then** une notification push est envoyée au gérant.
3. **Given** une notification d'éligibilité, **When** le gérant clique "Soumettre", **Then** un
   formulaire PDF pré-rempli est généré avec les pièces justificatives TRU TRACE.

---

### User Story 8 - Gestion de la reproduction et des naissances (Priority: P2)

Un agent enregistre une saillie, le système calcule automatiquement la date de mise bas prévue
selon l'espèce et alerte le berger responsable J-14 et J-7 avant l'échéance ; à la mise bas, les
fiches des nouveau-nés sont créées automatiquement avec filiation et identifiant TRU TRACE.

**Why this priority**: Impacte directement la croissance du cheptel et la donnée de filiation
utilisée par le planificateur de ventes et le dossier de financement.

**Independent Test**: Saisir une saillie bovine, vérifier le calcul de la date de mise bas (+283j),
les alertes J-14/J-7, puis simuler la mise bas et vérifier la création automatique des fiches
nouveau-nés.

**Acceptance Scenarios**:

1. **Given** une saillie bovine enregistrée, **When** elle est validée, **Then** la date de mise
   bas prévue est calculée automatiquement (+283 jours).
2. **Given** une mise bas enregistrée avec 2 nés vivants, **When** elle est validée, **Then** 2
   fiches animal sont créées automatiquement avec filiation et identifiant TRU TRACE.

---

### User Story 9 - Gestion des stocks et alertes de réapprovisionnement (Priority: P2)

Un comptable/gestionnaire suit les stocks d'aliments, médicaments et équipements, reçoit une
alerte au seuil de commande et une alerte de péremption des médicaments à moins de 30 jours.

**Why this priority**: Condition la fiabilité du coût alimentaire (P1 comptabilité) et évite les
ruptures critiques (vaccins, aliments) en zone rurale à approvisionnement irrégulier.

**Independent Test**: Simuler des sorties de stock jusqu'au seuil d'alerte, vérifier la
notification, et simuler un lot de médicaments à J-30 avant péremption.

**Acceptance Scenarios**:

1. **Given** un stock d'aliment atteignant son seuil de commande, **When** la sortie est
   enregistrée, **Then** une notification push est envoyée au comptable.
2. **Given** un lot de médicament à moins de 30 jours de péremption, **When** le système effectue
   sa vérification quotidienne, **Then** une alerte est générée.

---

### User Story 10 - Ventes et traçabilité commerciale TRU TRACE (Priority: P2)

Un gérant enregistre une vente d'animal, génère un bon de vente PDF partageable via WhatsApp, et
le système crée automatiquement un certificat de traçabilité TRU TRACE lié à l'UUID de l'animal,
transmis à l'acheteur.

**Why this priority**: Génère la donnée de CA nécessaire à la comptabilité (P1) et au dossier de
financement (P3), et valorise l'export via la traçabilité certifiée.

**Independent Test**: Enregistrer une vente complète, vérifier la génération du bon PDF et du
certificat TRU TRACE lié à l'UUID de l'animal vendu.

**Acceptance Scenarios**:

1. **Given** un animal vendu enregistré, **When** la vente est validée, **Then** un certificat de
   traçabilité TRU TRACE est généré et transmis automatiquement à l'acheteur.

### Edge Cases

- Que se passe-t-il si un utilisateur reste plus de 30 jours sans synchroniser ses données
  offline (volume de la file d'actions, risque de conflit massif) ?
- Comment le système gère-t-il une coupure réseau pendant la génération d'un dossier de
  financement (opération nécessairement en ligne) ?
- Que se passe-t-il si deux utilisateurs modifient simultanément la fiche financière du même
  animal hors ligne, chacun de son côté, avant synchronisation ?
- Comment le planificateur de ventes réagit-il si l'API prix marché RAB est indisponible au moment
  du calcul hebdomadaire (utilisation du dernier prix connu avec horodatage affiché) ?
- Qu'advient-il d'un membre de coopérative qui quitte la coopérative (rétractation du consentement
  et des accès du gestionnaire) ?
- Comment le système traite-t-il une demande de dossier de financement quand les 3 exercices
  comptables ne sont pas encore disponibles (exploitation trop récente) ?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Le système DOIT permettre la saisie et la consultation intégrale (cheptel, santé,
  stocks, alimentation, reproduction, RH, dépenses/recettes, ventes) sans connexion réseau sur
  mobile.
- **FR-002**: Le système DOIT synchroniser automatiquement les données locales dès reconnexion, en
  ne transmettant que les données modifiées (synchronisation delta).
- **FR-003**: Le système DOIT signaler au gérant tout conflit de synchronisation sur des données
  financières pour arbitrage manuel (pas de résolution automatique sur ces données).
- **FR-004**: Le système DOIT générer automatiquement le compte de résultat, le bilan simplifié et
  le tableau de trésorerie conformes au plan comptable SYSCOHADA révisé 2017.
- **FR-005**: Le système DOIT calculer automatiquement le coût de revient par animal et le seuil de
  rentabilité mensuel de l'exploitation.
- **FR-006**: Le système DOIT générer en moins de 60 secondes un dossier de financement PDF signé
  numériquement (SHA-256) et horodaté (RFC 3161), incluant comptes OHADA 3 ans, registre cheptel
  certifié TRU TRACE, bilan sanitaire MokineVeto et plan d'investissement.
- **FR-007**: Le système DOIT bloquer la génération du dossier de financement tant que les données
  des 3 derniers exercices ne sont pas complètes, et lister précisément les éléments manquants.
- **FR-008**: Le système DOIT importer automatiquement chaque consultation vétérinaire MokineVeto
  dans le DMA de l'animal correspondant, sans ressaisie.
- **FR-009**: Le système DOIT générer des alertes de vaccination à J-14 et J-3 avant échéance, et
  bloquer la mise en vente d'un animal dont le délai d'attente post-traitement n'est pas écoulé.
- **FR-010**: Le système DOIT calculer les recommandations de vente hebdomadaires en croisant
  données de croissance internes, coûts d'exploitation, cash flow et prix marché RAB Rwanda
  actualisés chaque vendredi 18h, en excluant animaux gestants, sous traitement, reproducteurs
  désignés et quota de sécurité défini par l'éleveur.
- **FR-011**: Le système DOIT afficher la date de dernière mise à jour des prix marché RAB utilisés
  dans toute recommandation de vente.
- **FR-012**: Le système DOIT permettre à un compte coopérative de regrouper jusqu'à 200 comptes
  membres, chaque membre conservant la gestion autonome et confidentielle de son exploitation.
- **FR-013**: Le système DOIT isoler les données de chaque membre de coopérative au niveau base de
  données (Row Level Security) et n'autoriser la consultation individuelle par le gestionnaire
  qu'après consentement explicite du membre.
- **FR-014**: Le système DOIT générer le rapport agrégé d'une coopérative de 200 membres en moins
  de 10 secondes.
- **FR-015**: Le système DOIT relancer automatiquement le moteur de règles d'éligibilité MINAGRI à
  chaque mise à jour significative des données de l'exploitation, et notifier le gérant de toute
  nouvelle éligibilité détectée.
- **FR-016**: Le système DOIT générer un formulaire PDF pré-rempli avec pièces justificatives
  TRU TRACE pour toute demande d'aide MINAGRI soumise par l'utilisateur.
- **FR-017**: Le système DOIT calculer automatiquement la date de mise bas prévue lors de
  l'enregistrement d'une saillie, selon la durée de gestation propre à l'espèce (bovins 283j,
  caprins 150j, ovins 147j, porcins 114j, camelins 390j), et alerter J-14/J-7.
- **FR-018**: Le système DOIT créer automatiquement les fiches animal des nouveau-nés (avec
  filiation et identifiant TRU TRACE) lors de l'enregistrement d'une mise bas.
- **FR-019**: Le système DOIT déduire automatiquement les stocks correspondants lors de chaque
  distribution alimentaire ou traitement sanitaire enregistré.
- **FR-020**: Le système DOIT générer une alerte de réapprovisionnement au seuil de commande
  configuré par produit, et une alerte de péremption pour tout médicament à moins de 30 jours.
- **FR-021**: Le système DOIT générer, pour chaque vente enregistrée, un certificat de traçabilité
  TRU TRACE lié à l'identifiant unique de l'animal, transmis automatiquement à l'acheteur.
- **FR-022**: Le système DOIT appliquer un contrôle d'accès basé sur les rôles (RBAC) restreignant
  l'accès aux données financières aux seuls rôles gérant et comptable.
- **FR-023**: Le système DOIT tenir un journal d'audit append-only, non modifiable, pour toute
  modification de données financières ou animales.

### Key Entities *(include if feature involves data)*

- **Exploitation**: Unité de gestion racine (nom, localisation GPS, espèces élevées, devise,
  langue). Peut être membre d'une Coopérative.
- **Animal**: Identifiant TRU TRACE unique, espèce, race, sexe, filiation, statut (actif/vendu/
  décédé/abattu/transféré), historique complet (santé, pesées, reproduction).
- **Utilisateur**: Rôle (gérant, agent terrain, comptable, gestionnaire coopérative, vétérinaire
  externe), rattaché à une Exploitation ou à une Coopérative.
- **Écriture comptable**: Dépense ou recette, catégorie SYSCOHADA, montant, justificatif, liée à un
  Animal, un Stock ou une Vente.
- **Dossier de financement**: Montant demandé, durée, objet, statut de complétude des 3 exercices,
  document PDF signé et horodaté.
- **Consultation vétérinaire (DMA)**: Origine MokineVeto ou saisie locale, animal concerné,
  diagnostic, traitement, délai d'attente abattage.
- **Recommandation de vente**: Animal concerné, score de priorité, marge journalière calculée,
  prix marché RAB de référence et sa date de fraîcheur.
- **Coopérative**: Regroupement de jusqu'à 200 Exploitations membres, avec consentements de
  consultation individuelle par membre.
- **Éligibilité MINAGRI**: Programme concerné, critères vérifiés, statut de la demande (éligible/
  soumis/approuvé/rejeté).
- **Stock**: Catégorie (aliments, médicaments, équipements, carburant), lot, seuil d'alerte, date
  de péremption, méthode de valorisation (FIFO/PMP).
- **Vente**: Animal(x) vendu(s), acheteur, prix, mode de paiement, certificat TRU TRACE associé.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% des saisies terrain listées (alimentation, pesée, saillie, mise bas, traitement,
  vaccination, achat, vente, dépense, présence) sont réalisables et persistées sans connexion
  réseau.
- **SC-002**: Le compte de résultat et le bilan simplifiés générés sont validés conformes SYSCOHADA
  révisé 2017 par un expert-comptable agréé OHADA.
- **SC-003**: Un dossier de financement complet est généré en moins de 60 secondes pour une
  exploitation avec 3 exercices comptables complets, y compris pour une coopérative de 200 membres.
- **SC-004**: La recommandation de vente hebdomadaire est disponible en moins de 5 secondes et
  atteint un taux de concordance supérieur à 70% avec les décisions d'un panel d'éleveurs experts
  rwandais sur 100 cas historiques.
- **SC-005**: Le rapport agrégé d'une coopérative de 200 membres est généré en moins de 10 secondes.
- **SC-006**: Aucun membre de coopérative ne peut accéder ou faire accéder à ses données
  individuelles sans consentement explicite préalable et vérifiable.
- **SC-007**: Le moteur d'éligibilité MINAGRI identifie correctement l'éligibilité sur 20 profils
  d'exploitation contrastés, validé par un agent MINAGRI partenaire.
- **SC-008**: Une exploitation type opère 30 jours consécutifs hors ligne sans perte de données à
  la synchronisation finale (intégrité financière et animale préservée à 100%).
- **SC-009**: L'écran d'accueil mobile se charge en moins de 2 secondes sur réseau 3G, et l'APK
  reste sous 40 Mo.

## Assumptions

- Les intégrations MokineVeto, TRU TRACE, MTN MoMo Rwanda, BPR Rwanda et MINAGRI exposent des API
  stables ; en cas d'indisponibilité, le système fonctionne en mode dégradé (cache/queue locale)
  sans bloquer l'usage quotidien.
- Les prix marché RAB Rwanda sont fournis via accord contractuel TRU GROUP × RAB, mis à jour chaque
  vendredi 18h ; en cas d'échec de mise à jour, le dernier prix connu est utilisé avec sa date
  affichée à l'utilisateur.
- Le marché cible principal (export) est le Rwanda ; le Cameroun est un marché domestique
  secondaire sans intégrations MINAGRI/BPR/RAB (modules spécifiques Rwanda désactivés hors zone).
- Les utilisateurs terrain disposent d'un smartphone Android 8.0 minimum ; aucun support iOS n'est
  requis en v1.1.
- La certification légale du dossier de financement (valeur probatoire RFC 3161) est acceptée par
  la BPR Rwanda sur la base d'un accord de validation partenaire déjà en place.
- Le volume cible de la phase 1 est de 10 000 exploitations actives, avec un maximum de 200 membres
  par coopérative.
