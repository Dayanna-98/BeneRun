# BeneRun

Application de gestion des benevoles pour l'association BeneRun.

Ce README est ecrit pour les debutants complets.
Suivez les etapes dans l'ordre, sans en sauter, et vous pourrez installer et lancer l'application.

## 1. Resume ultra rapide

Vous devez lancer 3 choses en meme temps:

1. API Laravel (backend): http://127.0.0.1:8000
2. Serveur temps reel Reverb (chat): port 8080
3. Frontend Vue (interface): http://localhost:5173

Donc, au final, vous aurez besoin de 3 terminaux ouverts en meme temps.

## 2. Prerequis (a installer une seule fois)

Sur Windows, installez:

1. Git: https://git-scm.com/download/win
2. Visual Studio Code: https://code.visualstudio.com/
3. XAMPP ou WampServer (pour MySQL):
	- XAMPP: https://www.apachefriends.org/fr/index.html
	- WampServer: https://www.wampserver.com/
4. Node.js LTS (recommande: 22.x): https://nodejs.org/
5. Composer: https://getcomposer.org/download/
6. PHP 8.2 ou plus

Versions attendues par le projet:

- PHP: 8.2+
- Node.js: 20.19+ ou 22.12+

### Verifier les installations

Ouvrez PowerShell et lancez:

~~~powershell
git --version
php -v
composer -V
node -v
npm -v
~~~

Si une commande n'est pas reconnue, l'outil n'est pas correctement installe ou non ajoute au PATH.

## 3. Ouvrir le bon dossier

Le dossier principal du projet est:

~~~text
BeneRun\BeneRun
~~~

Le frontend est dans:

~~~text
BeneRun\BeneRun\Front-end\maquette
~~~

Si vous devez cloner le projet:

~~~powershell
cd "C:\Users\votre_nom\Documents"
git clone https://github.com/Dayanna-98/BeneRun.git
cd "BeneRun\BeneRun"
~~~

## 4. Demarrer MySQL (obligatoire)

1. Ouvrez XAMPP ou WampServer.
2. Demarrez le service MySQL.
3. Laissez MySQL allume pendant toute l'utilisation de l'application.

Important:
Si MySQL est eteint, l'API renverra des erreurs 500.

## 5. Installation complete (premiere fois)

Cette partie se fait une seule fois sur une machine.

### 5.1 Backend Laravel

Placez-vous dans le dossier backend:

~~~powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun"
~~~

Installez les dependances PHP:

~~~powershell
composer install
~~~

Installez les dependances Node du backend:

~~~powershell
npm install
~~~

Creez le fichier d'environnement:

~~~powershell
Copy-Item .env.example .env
~~~

Generez la cle Laravel:

~~~powershell
php artisan key:generate
~~~

Si php n'est pas reconnu (cas frequent avec XAMPP), utilisez:

~~~powershell
& "c:\xampp3\php\php.exe" artisan key:generate
~~~

### 5.2 Verifier les variables backend

Dans le fichier .env, verifiez au minimum ces valeurs:

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

Puis creez/mettez a jour les tables:

~~~powershell
php artisan migrate --force
php artisan optimize:clear
~~~

### 5.3 Frontend Vue

Ouvrez un terminal dans le dossier frontend:

~~~powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun\Front-end\maquette"
~~~

Creez le fichier d'environnement frontend:

~~~powershell
Copy-Item .env.example .env
~~~

Verifiez ces valeurs:

~~~env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=benerun-local-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
~~~

Installez les dependances frontend:

~~~powershell
npm install
~~~

Rappel important:
Vous faites 2 fois npm install dans ce projet:

1. Dans BeneRun\BeneRun
2. Dans BeneRun\BeneRun\Front-end\maquette

## 6. Lancer l'application (a chaque session)

Vous devez ouvrir 3 terminaux et les laisser actifs.

### Terminal 1 - API Laravel

Dans BeneRun\BeneRun:

~~~powershell
php artisan serve
~~~

Alternative XAMPP:

~~~powershell
& "c:\xampp3\php\php.exe" artisan serve
~~~

### Terminal 2 - Reverb (temps reel/chat)

Dans BeneRun\BeneRun:

~~~powershell
php artisan reverb:start
~~~

Alternative XAMPP:

~~~powershell
& "c:\xampp3\php\php.exe" artisan reverb:start
~~~

### Terminal 3 - Frontend Vue

Dans BeneRun\BeneRun\Front-end\maquette:

~~~powershell
npm run dev
~~~

Ouvrez ensuite:

- http://localhost:5173

## 7. Verifier que tout fonctionne

1. Frontend ouvert sur http://localhost:5173
2. Backend actif sur http://127.0.0.1:8000
3. Documentation API visible sur http://127.0.0.1:8000/docs/api#/

## 8. Commandes utiles

### Backend (BeneRun\BeneRun)

~~~powershell
php artisan serve
php artisan reverb:start
php artisan migrate --force
php artisan optimize:clear
php artisan test
~~~

### Frontend (BeneRun\BeneRun\Front-end\maquette)

~~~powershell
npm run dev
npm run build
npm run preview
npm run lint
npm run test
~~~

## 9. Problemes frequents (et solutions)

### Probleme: php n'est pas reconnu

Utilisez directement PHP de XAMPP:

~~~powershell
& "c:\xampp3\php\php.exe" artisan serve
~~~

### Probleme: erreur 500 cote API

1. Verifiez que MySQL est bien demarre dans XAMPP/WampServer.
2. Verifiez DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD dans .env.
3. Relancez:

~~~powershell
php artisan migrate --force
php artisan optimize:clear
~~~

### Probleme: le frontend se lance mais n'affiche pas les donnees

1. Verifiez que Terminal 1 (php artisan serve) tourne.
2. Verifiez VITE_API_BASE_URL dans Front-end\maquette\.env.
3. Redemarrez le frontend avec npm run dev.

### Probleme: le chat temps reel ne marche pas

1. Verifiez que Terminal 2 (php artisan reverb:start) tourne.
2. Verifiez les REVERB_* dans .env backend.
3. Verifiez les VITE_REVERB_* dans .env frontend.

### Probleme: port deja utilise (8000, 5173 ou 8080)

Fermez le programme qui utilise le port, puis relancez les commandes.

## 10. Procedure de demarrage quotidienne (copier-coller)

1. Demarrer MySQL dans XAMPP/WampServer.
2. Ouvrir 3 terminaux.
3. Dans Terminal 1:

~~~powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun"
php artisan serve
~~~

4. Dans Terminal 2:

~~~powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun"
php artisan reverb:start
~~~

5. Dans Terminal 3:

~~~powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun\Front-end\maquette"
npm run dev
~~~

6. Ouvrir http://localhost:5173

## 11. Si vous etes bloque

Pour aider rapidement, partagez ces 3 elements:

1. La commande exacte executee
2. Le message d'erreur complet
3. Le resultat de:

~~~powershell
php -v
composer -V
node -v
npm -v
~~~

Avec ces informations, le diagnostic est beaucoup plus rapide.

