# SPÉCIFICATION FONCTIONNELLE DÉTAILLÉE — TRU FARM ERP

Référence: SFD-TFERP-2026-v1.1
Version: 1.1 — Intégration des 4 innovations benchmark
Date: Août 2026
Auteur: ZIEGOUBE FOKA Emmanuel — Gérant TRU GROUP SARL
Marché cible export: Rwanda — Cameroun (marché domestique)
Intégrations: MokineVeto · TRU TRACE · MTN MoMo Rwanda · BPR Rwanda · FAO/FIDA · MINAGRI Rwanda
Innovations v1.1: Dossier financement auto (BPR/FIDA) · Planificateur ventes IA · Mode coopérative Girinka · Éligibilité aides MINAGRI
Stack technique: Flutter 3.x (offline-first) · NestJS · PostgreSQL + TimescaleDB · SQLite (mobile) · AWS af-south-1

## 1. Contexte et objectifs

### 1.1 Problème à résoudre
99% des exploitations d'élevage rwandaises (< 200 têtes) sont gérées de façon empirique : aucun registre structuré, aucun suivi financier, aucune traçabilité sanitaire. Conséquences : décisions à l'aveugle, impossibilité d'accéder aux financements agricoles (BPR Rwanda, FIDA, BAD), exclusion des marchés d'exportation. Les solutions existantes (CattleMax, Farmbrite) sont inaccessibles aux PME africaines (49-199 USD/mois, cloud-only, anglophone). iCowsoft/Innoval (France) n'est pas adapté aux contextes OHADA. TRU FARM ERP est le premier ERP agropastoral conçu nativement pour les exploitations africaines de 5 à 500 têtes, avec comptabilité OHADA intégrée et mode coopérative Girinka natif.

### 1.2 Vision produit
Progiciel de gestion intégré (ERP) couvrant l'intégralité du cycle de gestion d'une exploitation d'élevage : animaux, santé, reproduction, alimentation, stocks, finances OHADA, ventes et RH. Mobile (offline-first) et web, intégré nativement avec MokineVeto et TRU TRACE.

### 1.3 Objectifs fonctionnels
- Centraliser toutes les données de l'exploitation dans un système unique accessible sans connexion permanente.
- Produire automatiquement des états financiers OHADA sans compétences comptables préalables.
- Générer en 1 clic un dossier de financement complet certifié (BPR Rwanda, FIDA, BAD).
- Recommander automatiquement les animaux à vendre ce mois-ci selon les prix marché RAB Rwanda en temps réel.
- Permettre à une coopérative Girinka de gérer les exploitations de 50 membres dans un seul tableau de bord consolidé.
- Vérifier automatiquement l'éligibilité aux programmes d'aide MINAGRI Rwanda et générer les formulaires pré-remplis.

## 2. Profils utilisateurs
- **Gérant/Propriétaire** : accès total, paramétrage, rapports financiers, export, dossier financement, planificateur ventes. Interface simple, mobile offline.
- **Agent d'élevage / Berger** : saisie animaux, alimentation, incidents sanitaires, lecture seule finances. Mobile offline obligatoire, interface ultra-simplifiée.
- **Comptable / Gestionnaire** : comptabilité OHADA complète, stocks, fournisseurs, rapports OHADA, dossier financement. Web prioritaire, export Excel/PDF.
- **Gestionnaire coopérative** : vue consolidée + individuelle par membre (50 membres), rapports agrégés, éligibilité aides MINAGRI. Web + mobile.
- **Vétérinaire (MokineVeto)** : lecture/écriture DMA uniquement via API MokineVeto, aucun accès finances.

## 3. Architecture fonctionnelle
- Mobile offline-first : Flutter 3.x + SQLite (Drift ORM).
- Web backoffice : React.js + Recharts + React-PDF.
- Backend API : NestJS, JWT, moteur de règles métier, moteur IA planificateur ventes, scheduler.
- BDD : PostgreSQL 16, TimescaleDB, SQLite local, Redis.
- Module IA planificateur ventes : Random Forest + règles métier.
- Intégrations : MokineVeto API, TRU TRACE API, MTN MoMo Rwanda, BPR Rwanda, MINAGRI Rwanda, FAO/FIDA.
- Offline & Sync : moteur bidirectionnel, gestion conflits (last-write-wins + arbitrage manuel finance).

## 4. Modules fonctionnels détaillés

### 4.1 Authentification & Gestion multi-utilisateurs
Création compte exploitation (nom, GPS, espèces, taille cheptel, devise FCFA, langue FR/EN/Kinyarwanda). Invitation utilisateurs par le gérant (SMS/email, rôle). Connexion mobile : téléphone + PIN 4 chiffres. Connexion web : email + mot de passe + OTP 2FA. Mode hors ligne : PIN validé localement (bcrypt SQLite). Mode démo avec exploitation fictive.

### 4.2 Tableau de bord exécutif
Mobile (Gérant) : chiffres clés (effectif cheptel/espèce, malades, recettes/dépenses du jour), 3 alertes prioritaires (vaccinations dues, stocks bas, ventes recommandées, échéances financières), accès 1 touche.
Web (Gérant/Comptable) : KPIs OHADA (CA mensuel, marge brute, trésorerie, bénéfice net, seuil rentabilité), KPIs zootechniques (mortalité, natalité, production laitière, GMQ, IC), tableau de bord coopérative, badge recommandations MINAGRI.

### 4.3 Gestion du cheptel
Fiche animal (ID TRU TRACE QR, espèce, race, sexe, naissance, filiation, statut). Photo + description physique offline. QR code imprimable. Historique complet (consultations, vaccinations, traitements, pesées, saillies, mises bas, ventes). Acquisitions (source, date, fournisseur, prix, origine, statut sanitaire). Sorties (motif, date, acheteur, prix, poids).

### 4.4 Suivi sanitaire & Vétérinaire (intégration MokineVeto)
Carnet de vaccination avec alertes J-14/J-3. Calendrier vaccinal automatique par espèce/région (OIE Rwanda). Campagnes de vaccination groupées. Registre des traitements avec délai d'attente abattage, alerte si animal sous traitement mis en vente. Import automatique des consultations MokineVeto dans le DMA.

### 4.5 Gestion de la reproduction
Saillies (mâle, femelle, date, type, opérateur). Calcul auto date mise bas (bovins 283j, caprins 150j, ovins 147j, porcins 114j, camelins 390j). Alertes J-14/J-7. Enregistrement mise bas (né vivants/morts-nés, poids, complications). Création auto fiches nouveau-nés + ID TRU TRACE. Tableau reproduction (fertilité, intervalle vêlage-vêlage, mortalité néonatale).

### 4.6 Gestion de l'alimentation & Nutrition
Rations par catégorie. Saisie quotidienne distributions avec déduction stock auto. Calcul coût alimentaire par animal/jour. Suivi production laitière (courbe lactation, alerte chute > 15%). Pesées (GMQ, IC, transmission TRU TRACE).

### 4.7 Gestion des stocks & Intrants
Catégories : aliments/fourrages, médicaments/vaccins (lot, péremption), équipements, carburant. Entrées (fournisseur, quantité, prix, BL). Sorties auto. Seuils d'alerte, alerte péremption < 30j. Valorisation FIFO ou PMP. Bon de commande PDF auto.

### 4.8 Comptabilité & Finance OHADA
Plan comptable OHADA simplifié (5 classes : Capitaux, Immobilisations, Stocks, Charges, Produits). Saisie simplifiée dépense/recette avec liens auto stock/ventes. Paiements fournisseurs via MTN MoMo Rwanda. États financiers auto : compte de résultat simplifié, tableau trésorerie, bilan simplifié annuel, coût de revient par animal, seuil de rentabilité.

### 4.9 Ressources humaines
Registre personnel. Suivi présences (pointage offline). Congés/absences. Calcul paie mensuelle + bulletin PDF. Productivité par agent.

### 4.10 Ventes & Traçabilité commerciale (intégration TRU TRACE)
Enregistrement vente (animal, acheteur, prix, date, lieu, paiement). Bon de vente/reçu PDF/WhatsApp. Facturation TVA optionnelle. Paiements différés + relance auto. Intégration TRU TRACE : certificat de traçabilité auto lié UUID animal, certificat OIE à l'acheteur. Carnet clients.

### 4.11 INNOVATION 1 — Dossier de financement auto (BPR Rwanda / FIDA)
Génère en 1 clic un dossier de financement complet certifié OHADA accepté par BPR Rwanda et FIDA/BAD.
Contenu : compte de résultat OHADA (3 ans), bilan simplifié OHADA (3 ans), tableau trésorerie prévisionnelle (3 ans), registre cheptel certifié TRU TRACE, bilan sanitaire certifié MokineVeto, plan d'investissement, attestation TRU GROUP.
Processus : saisie montant/durée/objet → vérification complétude 3 exercices → compilation auto multi-modules → génération PDF < 60s signé numériquement + horodatage RFC 3161 → téléchargement/soumission.

### 4.12 INNOVATION 2 — Planificateur de ventes intelligent (IA + prix marché RAB)
Combine données internes (poids, GMQ 30j, coût alimentaire/santé, âge, race), données externes (API RAB Rwanda, prix par espèce/race/poids/région, MAJ vendredi 18h), cash flow (trésorerie, charges fixes). Indicateur : marge journalière = (prix marché actuel − coût journalier accumulé) / poids vif. Modèle Random Forest fine-tuné (3 ans données RAB + ILRI). Résultat : liste 3-10 animaux priorisés avec estimation prix et CA total. Contraintes : traitement en cours, gestantes, reproducteurs désignés, quota sécurité.
Interface : écran "Vendre maintenant" avec score priorité 1-5, graphique optimisation valeur, alerte push hebdomadaire, bouton "Créer la vente maintenant".

### 4.13 INNOVATION 3 — Mode coopérative Girinka (jusqu'à 200 membres, 1 tableau de bord)
Compte "Coopérative" regroupant jusqu'à 200 comptes membres autonomes. Gestionnaire coopérative : dashboard consolidé (cheptel total, CA agrégé, mortalité moyenne, taux formation) + vue individuelle par membre (avec consentement).
Fonctionnalités : rapport d'impact coopérative (format FAO/FIDA/SNV/CARE), commandes groupées d'intrants, campagnes de vaccination groupées, dossier de financement coopératif consolidé, alerte solidarité (épizootie/mortalité massive).
Technique : PostgreSQL Row Level Security par coopérative, vue matérialisée pour agrégats, rapport généré < 10s pour 200 membres (cache Redis, recalcul 6h), format standard MINAGRI.

### 4.14 INNOVATION 4 — Éligibilité automatique aides MINAGRI Rwanda
7 programmes MINAGRI 2025-2026 intégrés : Girinka (One Cow per Poor Family), Subvention vaccins, Intrants subventionnés, Crédit RAB, Assurance bétail KLIP — chacun avec critères d'éligibilité vérifiés automatiquement et aide fournie.
Fonctionnement : moteur de règles relancé à chaque MAJ significative (nouvel animal, pesée, membre) → notification push si nouvelle éligibilité → génération formulaire pré-rempli PDF sur clic "Soumettre" → suivi statut demande (attente/approuvée/rejetée).

### 4.15 Rapports & Tableaux de bord analytiques
Rapport exécutif mensuel, compte de résultat OHADA, rapport trésorerie, dossier de financement complet (BPR/FIDA), rapport coopérative Girinka (200 membres), rapport performance zootechnique, rapport stocks.

### 4.16 Mode offline-first & Synchronisation
100% des saisies disponibles hors connexion (alimentation, pesée, saillie, mise bas, traitement, vaccination, achat, vente, dépense, présence). Consultation intégrale offline. Génération locale des alertes. Cache des 3 derniers rapports. Synchronisation auto à la reconnexion, résolution conflits (last-write-wins simple / arbitrage gérant pour finance). Sync delta (économie bande passante).

### 4.17 Administration & Paramétrage
Paramétrage exploitation (nom, logo, localisation, espèces, devise, langue, fuseau). Gestion rôles. Paramétrage alertes. Gestion espèces/races (races locales Ankole, Friesian croisé). Sauvegarde auto quotidienne AWS S3 chiffré. Clôture exercice annuel OHADA. Journal d'audit complet.

## 5. Exigences non fonctionnelles
- Chargement écran d'accueil < 2s sur 3G.
- Génération dossier financement PDF < 60s (200 membres coopérative).
- Calcul recommandation ventes IA < 5s.
- Android minimum 8.0 (API 26), APK < 40 Mo.
- Scalabilité : 10 000 exploitations, 200 membres/coopérative max.
- Stockage local < 150 Mo par exploitation.
- Uptime serveur 99.5% (maintenance dim. 02h-04h UTC).
- Prix marché RAB : MAJ hebdomadaire (vendredi 18h), date affichée.
- Conformité SYSCOHADA révisé 2017, dossier financement accepté BPR Rwanda.

## 6. Stack technique
Mobile: Flutter 3.x (Dart) + Drift ORM (SQLite). Web: React.js + Recharts + React-PDF + MUI. Backend: NestJS + TypeScript. BDD principale: PostgreSQL 16 (Row Level Security coopératives). Séries temporelles: TimescaleDB. BDD locale mobile: SQLite + Drift ORM. IA planificateur ventes: Python FastAPI + scikit-learn (Random Forest). PDF OHADA: PDFKit (Node.js) + Puppeteer. Paiements: MTN MoMo Rwanda API v2 + Airtel Money Rwanda. Hébergement: AWS af-south-1 + CloudFront + S3.

## 7. Sécurité & Conformité
TLS 1.3, AES-256 au repos (clés AWS KMS). RBAC strict par exploitation/rôle. Row Level Security PostgreSQL (isolation membres coopérative). Consentement explicite pour accès gestionnaire aux données individuelles. Conformité OHADA (SYSCOHADA révisé 2017) et Rwanda DPPA 2021. Dossiers de financement signés numériquement (SHA-256 + horodatage RFC 3161). Sauvegardes chiffrées quotidiennes S3 + snapshot hebdomadaire RDS. Journal d'audit append-only.

## 8. Plan de tests
Tests unitaires (calculs OHADA, alertes, GMQ/IC, éligibilité MINAGRI, algorithme planificateur) — Jest/pytest/Flutter Test. Tests intégration (flux complet acquisition→vente→rapport→dossier financement) — Supertest/Postman Newman. Tests OHADA (audit expert-comptable, validation BPR Rwanda). Tests offline 30 jours. Tests coopérative 50 membres (charge). Tests planificateur IA (concordance > 70% vs éleveurs experts, panel 5). Tests éligibilité MINAGRI (20 profils, validation agent MINAGRI). UAT terrain Rwanda (5 éleveurs + 2 coopératives, 8 semaines).

## 9. Modèle commercial & Tarification
- **Starter (gratuit, 6 mois)** : 20 animaux max, compta OHADA basique, pas de dossier financement/IA/coopérative, éligibilité MINAGRI basique, MokineVeto/TRU TRACE lecture seule, support email 72h.
- **Standard (200 000 FCFA/an)** : 200 animaux max, compta OHADA complète, dossier financement 2/an, planificateur IA, coopérative 50 membres, éligibilité MINAGRI complète, intégration bidirectionnelle, support WhatsApp 24h.
- **Premium (800 000 FCFA/an)** : illimité, compta + audit, dossier financement illimité, planificateur IA, coopérative 200 membres, éligibilité + soumission auto, intégration temps réel, support WhatsApp 4h + téléphone.
Tarification institutionnelle : FAO/FIDA Rwanda (contrat projet), MINAGRI Rwanda (licence institutionnelle 5-10M FCFA/an), ONG partenaires (SNV, CARE Rwanda, ACDI/VOCA).

## 10. Glossaire
ACDI/VOCA, BPR, CARE Rwanda, DPPA, FIDA, FIFO, Girinka, GMQ, ILRI, IC, KCC, MINAGRI, OHADA, PMP, RAB, RBAC, Row Level Security, SNV, SYSCOHADA, TimescaleDB — voir document source pour définitions complètes.
