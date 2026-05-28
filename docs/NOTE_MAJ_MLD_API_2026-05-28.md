# Note de passation - Mise a jour MLD et documentation API

Date: 2026-05-28
Auteur: Copilot (audit technique)
Perimetre: comparaison entre le document MLD textuel et le schema de base reel

## 1) Objectif

Donner une base claire pour mettre a jour, si necessaire:
- le MLD textuel
- la documentation API

Reference principale a verifier:
- [docs/MLD_TEXTUEL_BENERUN.md](docs/MLD_TEXTUEL_BENERUN.md)

## 2) Sources controlees

- Migrations Laravel dans [database/migrations](database/migrations)
- Schema MySQL reel via information_schema (base active)
- Statut des migrations via artisan migrate:status

## 3) Synthese executive

Le MLD est globalement bon (35 tables metier documentees), mais il existe quelques ecarts concrets:
- 3 colonnes id manquent dans le MLD (failed_jobs, jobs, personal_access_tokens)
- 1 colonne est documentee dans le MLD mais absente de la base active (affectations.heure_rendez_vous_affectation)
- 2 relations FK manquent dans la section relations (table mission_positions)
- 1 table technique supplementaire existe en base active (migrations), non incluse dans le MLD

## 4) Ecarts detailles a corriger dans le MLD

### 4.1 Colonnes manquantes dans le MLD

1. Table failed_jobs
- Ajouter: id
- Source: [database/migrations/0001_01_01_000002_create_jobs_table.php](database/migrations/0001_01_01_000002_create_jobs_table.php)

2. Table jobs
- Ajouter: id
- Source: [database/migrations/0001_01_01_000002_create_jobs_table.php](database/migrations/0001_01_01_000002_create_jobs_table.php)

3. Table personal_access_tokens
- Ajouter: id
- Source: [database/migrations/2026_04_16_212915_create_personal_access_tokens_table.php](database/migrations/2026_04_16_212915_create_personal_access_tokens_table.php)

### 4.2 Colonne a traiter selon environnement

Table affectations
- Colonne concernee: heure_rendez_vous_affectation
- Etat actuel: presente dans le MLD, absente dans la base active
- Cause: migration en attente
- Source migration: [database/migrations/2026_05_27_120000_add_heure_rendez_vous_to_affectations_table.php](database/migrations/2026_05_27_120000_add_heure_rendez_vous_to_affectations_table.php)

Decision documentaire a prendre:
- Si le MLD doit representer la base active immediate: retirer temporairement ce champ
- Si le MLD doit representer la cible apres migration: garder ce champ et appliquer la migration sur l environnement vise

### 4.3 Relations FK manquantes dans le MLD

Ajouter dans la section relations explicites:
- mission_positions.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- mission_positions.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)

Source:
- [database/migrations/2026_04_30_144542_create_mission_positions_table.php](database/migrations/2026_04_30_144542_create_mission_positions_table.php)

### 4.4 Table technique absente du MLD

- Table presente en base: migrations
- Cette table est technique (pilotage des migrations)

Decision documentaire:
- Option A: ne pas l ajouter si le MLD reste metier/fonctionnel
- Option B: l ajouter si l objectif est inventaire exhaustif physique

## 5) Impact sur la documentation API

Point principal a verifier dans la doc API:
- Cohesion autour du champ affectations.heure_rendez_vous_affectation

Pourquoi:
- Si la migration 2026_05_27_120000 n est pas appliquee, des endpoints qui lisent/ecrivent ce champ peuvent diverger de la base active.

Actions recommandees sur la doc API:
1. Pour les endpoints affectations (creation, modification, lecture), preciser le statut du champ heure_rendez_vous_affectation:
- disponible uniquement si migration appliquee
- format attendu HH:MM:SS (ou HH:MM selon normalisation backend)

2. Ajouter un pre-requis de version schema dans la doc:
- migration requise: 2026_05_27_120000_add_heure_rendez_vous_to_affectations_table

3. Si la documentation publique doit rester stable multi environnements, ajouter un encart de compatibilite:
- Environnement non migre: champ absent
- Environnement migre: champ present

## 6) Proposition de plan de mise a jour

1. Mettre a jour [docs/MLD_TEXTUEL_BENERUN.md](docs/MLD_TEXTUEL_BENERUN.md):
- + id dans failed_jobs
- + id dans jobs
- + id dans personal_access_tokens
- + 2 FK manquantes de mission_positions
- Gerer le cas heure_rendez_vous_affectation selon decision d environnement

2. Mettre a jour la doc API de reference:
- verifier et ajuster [docs/REFERENCE_ROUTES_API_BENERUN.md](docs/REFERENCE_ROUTES_API_BENERUN.md)
- harmoniser les payloads affectations avec le statut de migration

3. Verification finale:
- relancer le comparatif schema vs MLD
- confirmer qu il n y a plus d ecart non justifie

## 7) Checklist de validation rapide

- Le MLD contient toutes les tables metier attendues
- Les colonnes id de jobs/failed_jobs/personal_access_tokens sont bien presentes
- Les FK de mission_positions sont bien documentees
- Le choix sur heure_rendez_vous_affectation est explicite (actuel vs cible)
- La doc API mentionne clairement la dependance a la migration affectations

## 8) Notes de contexte utiles

Statut migration observe pendant l audit:
- migration en attente: 2026_05_27_120000_add_heure_rendez_vous_to_affectations_table

Ce point explique l ecart principal entre MLD et base active.
