# Guide d installation BeneRun (Windows) - V2

Version: 2026-05-28
Public vise: debutants, nouveaux membres projet, profils non techniques

Ce guide est concu pour etre suivi pas a pas. Si vous appliquez les etapes dans l ordre, vous obtenez une installation locale fonctionnelle.

## 1) Objectif en 1 minute

Vous allez lancer 3 services en meme temps:

1. API Laravel (backend): http://127.0.0.1:8000
2. Serveur temps reel Reverb (chat): port 8080
3. Frontend Vue (interface): http://localhost:5173

Si les 3 services tournent, l application fonctionne.

## 2) Prerequis obligatoires

Installez ces outils avant toute chose:

- Git
- PHP 8.2 ou plus
- Composer
- Node.js 20.19+ ou 22.12+
- npm
- MySQL (XAMPP ou WampServer recommande sous Windows)

## Verification immediate

Ouvrez PowerShell et lancez:

```powershell
git --version
php -v
composer -V
node -v
npm -v
```

Resultat attendu:
- chaque commande retourne une version
- aucune commande ne doit afficher "n est pas reconnu"

Si une commande n est pas reconnue, arretez ici et corrigez l installation de l outil concerne.

## 3) Bien choisir les dossiers

Le projet utilise 2 dossiers differents:

- Backend Laravel: BeneRun/BeneRun
- Frontend principal: BeneRun/BeneRun/Front-end/maquette

Point critique:
- vous devez executer npm install dans les 2 dossiers

## 4) Premiere installation (a faire une seule fois)

## Etape A - Demarrer MySQL

1. Ouvrez XAMPP ou WampServer.
2. Demarrez MySQL.

Resultat attendu:
- MySQL est en statut "Running".

## Etape B - Installer le backend

Dans un terminal PowerShell:

```powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun"
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan optimize:clear
```

Resultat attendu:
- pas d erreur bloquante sur composer install
- pas d erreur bloquante sur npm install
- commande key:generate reussie
- migrations terminees

Si php n est pas reconnu (frequent sous Windows/XAMPP):

```powershell
& "c:\xampp3\php\php.exe" artisan key:generate
& "c:\xampp3\php\php.exe" artisan migrate --force
& "c:\xampp3\php\php.exe" artisan optimize:clear
```

## Etape C - Verifier .env backend

Dans BeneRun/BeneRun/.env, verifiez au minimum:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=benerun
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=benerun-local
REVERB_APP_KEY=benerun-local-key
REVERB_APP_SECRET=benerun-local-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

FRONTEND_URL=http://localhost:5173
```

## Etape D - Installer le frontend principal

Dans un nouveau terminal:

```powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun\Front-end\maquette"
Copy-Item .env.example .env
npm install
```

Dans Front-end/maquette/.env, verifiez:

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=benerun-local-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

Resultat attendu:
- npm install se termine sans erreur bloquante

## 5) Demarrage quotidien (a chaque session)

Avant de lancer l app:

1. demarrer MySQL
2. ouvrir 3 terminaux

Terminal 1 - Backend API:

```powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun"
php artisan serve
```

Terminal 2 - Reverb (chat temps reel):

```powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun"
php artisan reverb:start
```

Terminal 3 - Frontend:

```powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun\Front-end\maquette"
npm run dev
```

Alternative XAMPP pour les terminaux 1 et 2:

```powershell
& "c:\xampp3\php\php.exe" artisan serve
& "c:\xampp3\php\php.exe" artisan reverb:start
```

## 6) Validation finale (obligatoire)

Confirmez ces 4 points:

1. Frontend accessible: http://localhost:5173
2. Backend actif: http://127.0.0.1:8000
3. Doc API accessible: http://127.0.0.1:8000/docs/api#/
4. Le frontend affiche des donnees (pas ecran vide)

Si un point est faux, passez a la section depannage.

## 7) Depannage ultra-pratique

## Symptome A - Erreur 500 cote API

Causes les plus probables:
- MySQL non demarre
- mauvais DB_* dans .env
- migrations manquantes

Actions immediates:

```powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun"
php artisan migrate --force
php artisan optimize:clear
```

Puis reverifiez que MySQL est bien en cours d execution.

## Symptome B - Frontend ouvert mais vide

Causes les plus probables:
- backend non demarre
- VITE_API_BASE_URL incorrect

Actions:
1. verifier que Terminal 1 tourne
2. verifier Front-end/maquette/.env
3. relancer npm run dev

## Symptome C - Chat non temps reel

Causes les plus probables:
- reverb non demarre
- variables Reverb backend/frontend incoherentes

Actions:
1. verifier Terminal 2 (reverb)
2. comparer REVERB_* (backend) et VITE_REVERB_* (frontend)

## Symptome D - php non reconnu

Action:

```powershell
& "c:\xampp3\php\php.exe" artisan serve
```

## Symptome E - Port deja utilise (8000, 8080, 5173)

Action:
- fermer le process qui occupe le port
- relancer les commandes

## 8) Commandes utiles

Backend:

```powershell
php artisan serve
php artisan reverb:start
php artisan migrate --force
php artisan optimize:clear
php artisan test
```

Frontend principal:

```powershell
npm run dev
npm run build
npm run preview
npm run lint
npm run test
```

## 9) Configuration mail (optionnelle en local)

Pour tester les flux email (reset/verification), configurez:

- MAIL_MAILER
- MAIL_HOST
- MAIL_PORT
- MAIL_USERNAME
- MAIL_PASSWORD
- MAIL_FROM_ADDRESS
- MAIL_FROM_NAME
- FRONTEND_RESET_PASSWORD_URL_TEMPLATE (optionnel)
- FRONTEND_EMAIL_VERIFICATION_URL_TEMPLATE (optionnel)

Si vous ne testez pas les emails en local, vous pouvez laisser la configuration par defaut.

## 10) Checklist "pret a coder"

Avant de commencer le dev, tout doit etre vrai:

- prerequis installes et versions visibles
- MySQL demarre
- .env backend cree et complete
- .env frontend cree et complete
- migrations executees
- 3 services actifs (serve, reverb, vite)
- http://localhost:5173 accessible

## 11) Si vous etes bloque, quoi envoyer

Pour un diagnostic rapide, partagez:

1. la commande exacte executee
2. le message d erreur complet
3. ces sorties:

```powershell
php -v
composer -V
node -v
npm -v
```

Avec ces informations, la resolution est generalement rapide.
