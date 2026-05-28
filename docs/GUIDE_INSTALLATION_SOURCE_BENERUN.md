# Dossier source - Guide d installation BeneRun

Date de generation: 2026-05-28

But: fournir toutes les informations verifiees necessaires pour rediger ou maintenir un guide d installation de BeneRun, pour un public debutant a intermediaire.

Regle de confiance: uniquement des donnees observees dans le code et la documentation du depot.

## 1) Perimetre exact du guide installation

Le guide d installation doit couvrir:
- prerequis machine
- installation backend Laravel
- installation frontend Vue principal
- configuration .env backend et frontend
- demarrage quotidien
- verification de bon fonctionnement
- depannage courant

Le guide doit traiter le contexte Windows en priorite (recommandations README), sans exclure les principes generiques.

## 2) Sources verifiees

Sources principales utilisees:
- README.md (racine backend)
- Front-end/maquette/README.md
- .env.example (backend)
- Front-end/maquette/.env.example
- composer.json
- package.json (racine)
- Front-end/maquette/package.json
- docs/DOSSIER_SOURCE_MANUEL_TECHNIQUE_BENERUN.md (synthese technique)

## 3) Prerequis techniques verifies

## 3.1 Outils
- Git
- Visual Studio Code (recommande dans README)
- PHP >= 8.2
- Composer
- Node.js (>=20.19.0 ou >=22.12.0 selon frontend)
- npm
- MySQL (README recommande XAMPP ou WampServer sous Windows)

## 3.2 Compatibilites/versionning
- Backend: Laravel 12 (composer.json)
- Requis PHP backend: ^8.2 (composer.json)
- Front principal: Vue 3 + Vite (Front-end/maquette/package.json)
- Node moteur front principal: ^20.19.0 || >=22.12.0 (Front-end/maquette/package.json)

## 4) Dossiers a ne pas confondre

- Backend Laravel: BeneRun/BeneRun
- Frontend principal: BeneRun/BeneRun/Front-end/maquette

Point critique: il faut installer des dependances npm dans les 2 dossiers (backend + frontend principal).

## 5) Variables d environnement a documenter

## 5.1 Backend (.env)
Obligatoires pour un setup local standard:
- APP_URL=http://localhost:8000
- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_DATABASE=benerun
- DB_USERNAME=root
- DB_PASSWORD=
- BROADCAST_CONNECTION=reverb
- REVERB_APP_ID=benerun-local
- REVERB_APP_KEY=benerun-local-key
- REVERB_APP_SECRET=benerun-local-secret
- REVERB_HOST=127.0.0.1
- REVERB_PORT=8080
- REVERB_SCHEME=http
- FRONTEND_URL=http://localhost:5173

Utiles selon contexte:
- MAIL_* (SMTP)
- FRONTEND_RESET_PASSWORD_URL_TEMPLATE
- FRONTEND_EMAIL_VERIFICATION_URL_TEMPLATE

## 5.2 Frontend principal (Front-end/maquette/.env)
Obligatoires:
- VITE_API_BASE_URL=http://localhost:8000/api
- VITE_REVERB_APP_KEY=benerun-local-key
- VITE_REVERB_HOST=127.0.0.1
- VITE_REVERB_PORT=8080
- VITE_REVERB_SCHEME=http

## 6) Procedure premiere installation (verifiee)

1. Demarrer MySQL local.
2. Installer backend:
   - composer install
   - npm install
   - copy .env.example -> .env
   - php artisan key:generate
   - php artisan migrate --force
   - php artisan optimize:clear
3. Installer frontend principal:
   - aller dans Front-end/maquette
   - copy .env.example -> .env
   - npm install

## 7) Procedure de demarrage quotidien (verifiee)

Trois terminaux a garder actifs:
- terminal A: php artisan serve (backend)
- terminal B: php artisan reverb:start (temps reel/chat)
- terminal C: npm run dev (Front-end/maquette)

URLs de verification:
- Frontend: http://localhost:5173
- Backend API: http://127.0.0.1:8000
- Documentation API: http://127.0.0.1:8000/docs/api#/

## 8) Commandes de verification et maintenance

Verification prerequis:
- git --version
- php -v
- composer -V
- node -v
- npm -v

Backend:
- php artisan serve
- php artisan reverb:start
- php artisan migrate --force
- php artisan optimize:clear
- php artisan test

Frontend principal:
- npm run dev
- npm run build
- npm run preview
- npm run lint
- npm run test

## 9) Particularites Windows explicites

Cas frequent: php non reconnu dans PATH.
Commande alternative documentee:
- & "c:\xampp3\php\php.exe" artisan serve
- & "c:\xampp3\php\php.exe" artisan reverb:start
- & "c:\xampp3\php\php.exe" artisan key:generate

Point de vigilance: sans l operateur PowerShell &, le chemin PHP XAMPP peut echouer.

## 10) Symptomes frequents et causes probables

- Erreur API 500 generalisee:
  - MySQL arrete
  - DB_* incorrect dans .env
  - migrations non appliquees

- Frontend lance mais sans donnees:
  - backend non demarre
  - VITE_API_BASE_URL incorrect

- Chat temps reel inactif:
  - reverb non demarre
  - REVERB_* backend et VITE_REVERB_* frontend incoherents

- Port occupe (8000/8080/5173):
  - conflit avec un autre process local

## 11) Differencier obligatoire vs optionnel

Obligatoire pour run local fonctionnel:
- MySQL en service
- backend installe + .env configure + migrations
- frontend principal installe + .env configure
- 3 process actifs (serve, reverb, vite)

Optionnel selon usage:
- configuration mail SMTP reelle
- templates FRONTEND_*_URL_TEMPLATE
- build frontend de production
- lint/tests avant dev local

## 12) Checklists prêtes a reutiliser

## 12.1 Checklist pre-install
- Outils installes et versions OK
- MySQL demarre
- Acces au dossier backend et au dossier frontend principal

## 12.2 Checklist post-install
- .env backend present
- .env frontend principal present
- migrations executees
- serve + reverb + vite lances
- ouverture de http://localhost:5173

## 12.3 Checklist diagnostic rapide
- commande executee exacte
- message d erreur complet
- sortie php -v / composer -V / node -v / npm -v
- verification .env backend/frontend

## 13) Limites et informations non affirmees

- Ce dossier ne decrit pas un deploiement production complet (reverse proxy, SSL, process manager) faute de specification complete dans les sources d installation.
- Les commandes sont valides pour l usage local tel que documente dans les README du projet.

Ce fichier est la base de verite pour rediger ou maintenir un guide d installation BeneRun de qualite projet.
