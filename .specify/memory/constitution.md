# TRU FARM ERP Constitution

## Core Principles

### I. Offline-First, Toujours
Toute saisie terrain (distribution alimentaire, pesée, saillie, mise bas, traitement, vaccination,
achat, vente, dépense, présence employé) DOIT fonctionner à 100% sans connexion internet sur mobile.
La consultation des fiches animaux, DMA, stocks et fiches employés DOIT rester intégrale hors ligne.
Les alertes (vaccinations dues, stocks bas, recommandations de vente) DOIVENT être calculables
localement, sans dépendance serveur. Aucune fonctionnalité de saisie terrain ne peut être conçue
comme "online-only" : le réseau est un bonus de synchronisation, jamais un prérequis d'usage.

### II. Conformité OHADA Non Négociable
Tous les calculs et documents comptables (compte de résultat, bilan simplifié, tableau de trésorerie,
coût de revient, seuil de rentabilité, dossier de financement) DOIVENT respecter le plan comptable
SYSCOHADA révisé 2017. Toute divergence avec les normes OHADA est un bug bloquant, pas un compromis
acceptable. Les évolutions du module comptable DOIVENT être validées par un expert-comptable agréé
OHADA avant mise en production.

### III. Sécurité, Isolation des Données & Consentement
RBAC strict par exploitation et par rôle. Isolation des données de coopérative par Row Level
Security PostgreSQL : un membre ne voit jamais les données d'un autre membre sans consentement
explicite et préalable. Chiffrement TLS 1.3 en transit, AES-256 au repos (clés AWS KMS). Toute
fonctionnalité donnant accès à des données au-delà du périmètre strict de l'utilisateur (ex. :
gestionnaire de coopérative consultant un membre) DOIT passer par un mécanisme de consentement
vérifiable, jamais par un accès implicite.

### IV. Traçabilité & Intégrité Probante (NON-NEGOTIABLE)
Le journal d'audit des données financières et animales est append-only et non modifiable. Les
dossiers de financement générés (BPR Rwanda, FIDA) DOIVENT être signés numériquement (SHA-256) et
horodatés (RFC 3161) pour conserver une valeur probatoire légale. Aucune fonctionnalité ne peut
permettre la suppression silencieuse ou la réécriture d'un enregistrement financier ou sanitaire
déjà validé — seule la correction par écriture complémentaire tracée est autorisée.

### V. Simplicité d'Usage pour Utilisateurs Peu Experts
L'utilisateur cible principal (gérant d'exploitation, berger) n'a pas d'expertise numérique ni
comptable. Chaque écran mobile terrain privilégie pictogrammes, résumés et actions en 1 touche sur
les données brutes. Toute fonctionnalité complexe (comptabilité, IA, dossier de financement) DOIT
être accessible sans compétence préalable : le système fait le travail expert, l'utilisateur ne
fait que confirmer des décisions simples.

### VI. Intégrations Externes comme Dépendances Dégradables
Les intégrations tierces (MokineVeto, TRU TRACE, MTN MoMo Rwanda, BPR Rwanda, MINAGRI, API RAB) sont
traitées comme des dépendances externes potentiellement indisponibles. Chaque intégration DOIT avoir
un mode dégradé (queue locale, données mises en cache, ressaisie manuelle possible) qui n'empêche
jamais l'utilisateur de continuer son travail quotidien sur l'exploitation.

### VII. Architecture Modulaire Multi-plateforme
Le système est découpé en modules fonctionnels indépendants (cheptel, santé, reproduction,
alimentation, stocks, comptabilité, RH, ventes, innovations IA/financement/coopérative/éligibilité).
Chaque module expose un contrat d'API clair (NestJS) consommé indifféremment par le mobile Flutter
offline-first et le web React. Un module ne doit jamais supposer une implémentation UI spécifique.

## Contraintes Techniques & Performance

Stack imposée : Flutter 3.x + Drift/SQLite (mobile), React.js + Recharts + React-PDF (web),
NestJS + TypeScript (backend), PostgreSQL 16 + TimescaleDB (données + séries temporelles), Redis
(cache/sessions), Python FastAPI + scikit-learn (microservice IA planificateur de ventes), AWS
af-south-1 (hébergement données africaines).

Seuils de performance non négociables : écran d'accueil < 2s sur 3G, génération dossier de
financement PDF < 60s pour une coopérative de 200 membres, recommandation IA de ventes < 5s, APK
< 40 Mo, stockage local < 150 Mo par exploitation, uptime serveur ≥ 99,5%. Ces seuils sont des
critères d'acceptation de fonctionnalité, pas des objectifs d'optimisation ultérieure.

## Workflow de Développement & Qualité

Toute fonctionnalité touchant à la comptabilité, au dossier de financement ou à l'éligibilité
MINAGRI DOIT inclure des tests unitaires de calcul avant merge (Jest/pytest/Flutter Test), et une
revue de non-régression sur les scénarios OHADA existants. Les fonctionnalités offline DOIVENT être
testées en coupure réseau simulée (saisies 30 jours puis synchronisation) avant d'être considérées
terminées. Le planificateur de ventes IA et le moteur d'éligibilité MINAGRI DOIVENT être validés
contre des jeux de données de référence (panel éleveurs Rwanda / profils MINAGRI) avant activation
en production. Aucune fonctionnalité affectant l'isolation des données de coopérative ne peut être
mergée sans revue de sécurité explicite des règles Row Level Security concernées.

## Governance

Cette constitution prévaut sur toute pratique de développement ou préférence individuelle en cas de
conflit. Toute proposition de plan ou de tâche qui contredit un principe ci-dessus DOIT soit être
reformulée pour s'y conformer, soit faire l'objet d'une dérogation explicitement justifiée et
documentée dans le plan correspondant (section Complexity Tracking). Les amendements à cette
constitution nécessitent une justification écrite, un impact documenté sur les specs/plans actifs,
et la mise à jour de cette version.

**Version**: 1.0.0 | **Ratified**: 2026-09-16 | **Last Amended**: 2026-09-16
