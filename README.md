# BeneRun

Application de gestion des benevoles pour l'association Béné'Run.

Ce guide est volontairement tres detaille pour une personne debutante.
Si vous suivez chaque etape dans l'ordre, vous pourrez installer et lancer l'application en local.

## 1. Ce que vous allez lancer

L'application locale a besoin de 3 programmes en meme temps:

1. API Laravel (backend) sur http://127.0.0.1:8000
2. Serveur WebSocket Reverb (messagerie temps reel) sur le port 8080
3. Application Vue (frontend) sur http://localhost:5173

## 2. Prerequis (a installer une seule fois)

Sur Windows, installez:

1. Git
2. Visual Studio Code
3. XAMPP ou WAMP (pour MySQL)
4. PHP 8.2 ou plus
5. Composer
6. Node.js (version 20.19+ ou 22.12+)

Checklist concrete des installations Windows:

1. Installez Git (https://git-scm.com/download/win)
2. Installez Visual Studio Code (https://code.visualstudio.com/)
3. Installez XAMPP (https://www.apachefriends.org/fr/index.html) ou WampServer (https://www.wampserver.com/)
4. Installez Node.js LTS 22.x (https://nodejs.org/)
5. Installez Composer (https://getcomposer.org/download/)
6. Verifiez que PHP est disponible dans le terminal (sinon utilisez le binaire XAMPP comme indique plus bas)

Verification rapide dans PowerShell:

~~~powershell
git --version
php -v
composer -V
node -v
npm -v
~~~

Si une commande n'est pas reconnue, l'outil n'est pas encore installe (ou pas dans le PATH).

## 3. Recuperer le projet

Si vous n'avez pas encore le code:

~~~powershell
cd "C:\Users\votre_nom\Documents"
git clone https://github.com/Dayanna-98/BeneRun.git
cd "BeneRun\BeneRun"
~~~

Important:

- Le dossier de travail pour Laravel est `BeneRun\BeneRun`.
- Le dossier frontend est `BeneRun\BeneRun\Front-end\maquette`.

## 4. Demarrer la base de donnees (XAMPP/WAMP)

1. Ouvrez XAMPP/WAMP.
2. Demarrez MySQL.
3. Verifiez que le port correspond a `DB_PORT` (3306 par defaut).

Astuce:
Si toutes les routes API repondent en erreur 500, le service MySQL est souvent arrete.

## 5. Configurer le backend Laravel

Placez-vous dans le dossier backend:

~~~powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun"
~~~

### 5.1 Installer les dependances PHP

~~~powershell
composer install
~~~

### 5.1 bis Installer les dependances Node du backend

Le dossier backend contient aussi un `package.json` (assets Laravel/Vite).

~~~powershell
npm install
~~~

### 5.2 Creer le fichier .env

~~~powershell
Copy-Item .env.example .env
~~~

### 5.3 Generer la cle Laravel

~~~powershell
php artisan key:generate
~~~

Si la commande `php` ne fonctionne pas mais que vous utilisez XAMPP:

~~~powershell
& "c:\xampp3\php\php.exe" artisan key:generate
~~~

### 5.4 Verifier les variables importantes dans .env

Le fichier `BeneRun\.env` doit au minimum contenir:

~~~env
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
~~~

### 5.5 Creer les tables de la base

~~~powershell
php artisan migrate --force
php artisan optimize:clear
~~~

## 6. Configurer le frontend Vue

Ouvrez un terminal dans le dossier frontend:

~~~powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun\Front-end\maquette"
~~~

### 6.1 Creer le .env frontend

~~~powershell
Copy-Item .env.example .env
~~~

Contenu attendu:

~~~env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=benerun-local-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
~~~

### 6.2 Installer les dependances Node

~~~powershell
npm install
~~~

Important:

- Vous aurez donc 2 installations npm au total:
1. `npm install` dans `BeneRun\BeneRun`
2. `npm install` dans `BeneRun\BeneRun\Front-end\maquette`

## 7. Lancer l'application (3 terminaux)

Vous devez laisser les 3 commandes ouvertes.

### Terminal A - API Laravel

Depuis `BeneRun\BeneRun`:

~~~powershell
php artisan serve
~~~

### Terminal B - WebSocket Reverb

Depuis `BeneRun\BeneRun`:

~~~powershell
php artisan reverb:start
~~~

### Terminal C - Frontend Vue

Depuis `BeneRun\BeneRun\Front-end\maquette`:

~~~powershell
npm run dev
~~~

Ensuite, ouvrez dans votre navigateur:

- http://localhost:5173

## 8. Verification rapide

Si tout est correct:

1. La page frontend s'ouvre sur http://localhost:5173
2. L'API repond sur http://127.0.0.1:8000
3. La doc API est visible sur http://127.0.0.1:8000/docs/api#/

## 9. Problemes frequents et solutions

### 9.1 `php` n'est pas reconnu

Utilisez le binaire XAMPP directement:

~~~powershell
& "c:\xampp3\php\php.exe" artisan serve
~~~

### 9.2 Erreurs SQL ou API 500

1. Verifiez que MySQL est demarre dans XAMPP/WAMP
2. Verifiez les valeurs `DB_*` dans `.env`
3. Relancez:

~~~powershell
php artisan migrate --force
php artisan optimize:clear
~~~

### 9.3 Le frontend ne charge pas les donnees

1. Verifiez que `php artisan serve` est actif
2. Verifiez `VITE_API_BASE_URL=http://localhost:8000/api`
3. Relancez le frontend `npm run dev`

### 9.4 La messagerie temps reel ne marche pas

1. Verifiez que `php artisan reverb:start` tourne
2. Verifiez les variables `REVERB_*` dans le backend
3. Verifiez les variables `VITE_REVERB_*` dans le frontend

## 10. Commandes utiles

Backend (dans `BeneRun\BeneRun`):

~~~powershell
php artisan serve
php artisan reverb:start
php artisan migrate --force
php artisan test
~~~

Frontend (dans `BeneRun\BeneRun\Front-end\maquette`):

~~~powershell
npm run dev
npm run build
npm run preview
npm run lint
~~~

## 11. Aide

En cas de blocage, partagez:

1. La commande executee
2. Le message d'erreur complet
3. Le resultat de `php -v`, `composer -V`, `node -v`, `npm -v`

Avec ces 3 informations, le diagnostic est beaucoup plus rapide.

