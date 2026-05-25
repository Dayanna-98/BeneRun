# Front-end BeneRun (maquette)

Guide debutant pour installer et lancer le front-end Vue du projet.

Ce dossier contient uniquement l'interface utilisateur.
Le backend Laravel doit aussi etre lance pour que les donnees s'affichent.

## 1. Prerequis

Installez ces outils:

1. Node.js (version 20.19+ ou 22.12+)
2. npm (installe avec Node.js)
3. PHP 8.2+
4. Composer
5. XAMPP ou WAMP (MySQL)
6. Git

Liens d'installation (Windows):

1. Node.js: https://nodejs.org/
2. Composer: https://getcomposer.org/download/
3. XAMPP: https://www.apachefriends.org/fr/index.html
4. WampServer: https://www.wampserver.com/
5. Git: https://git-scm.com/download/win

Verification rapide:

~~~powershell
node -v
npm -v
php -v
composer -V
~~~

## 2. Ouvrir le bon dossier

Dans PowerShell:

~~~powershell
cd "C:\Users\votre_nom\Documents\BeneRun\BeneRun\Front-end\maquette"
~~~

Important:

- Les commandes `npm` se font ici, dans `Front-end\maquette`.
- Les commandes `php artisan ...` se font dans `BeneRun\BeneRun`.

## 3. Configurer le .env frontend

Si `.env` n'existe pas, creez-le:

~~~powershell
Copy-Item .env.example .env
~~~

Valeurs attendues:

~~~env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=benerun-local-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
~~~

## 4. Installer les dependances front

~~~powershell
npm install
~~~

Important pour que tout tourne correctement:

1. Dans `BeneRun\BeneRun`, faites aussi `composer install`
2. Dans `BeneRun\BeneRun`, faites aussi `npm install`
3. Puis dans `BeneRun\BeneRun\Front-end\maquette`, faites `npm install`

Sans ces 3 installations, vous pouvez avoir des erreurs au demarrage.

## 5. Lancer l'application complete

Le front depend du backend. Il faut 3 terminaux ouverts.

### Terminal 1 - API Laravel

Dans `BeneRun\BeneRun`:

~~~powershell
php artisan serve
~~~

### Terminal 2 - Reverb (messagerie temps reel)

Dans `BeneRun\BeneRun`:

~~~powershell
php artisan reverb:start
~~~

### Terminal 3 - Frontend Vue

Dans `BeneRun\BeneRun\Front-end\maquette`:

~~~powershell
npm run dev
~~~

URL front:

- http://localhost:5173

## 6. Verification rapide

Si tout est OK:

1. Le navigateur ouvre http://localhost:5173
2. Le terminal front affiche que Vite est pret
3. Le backend repond sur http://127.0.0.1:8000

## 7. Commandes utiles

Dans `Front-end\maquette`:

~~~powershell
npm run dev
npm run build
npm run preview
npm run lint
npm run test
~~~

Dans `BeneRun\BeneRun`:

~~~powershell
php artisan serve
php artisan reverb:start
php artisan migrate --force
~~~

## 8. Depannage simple

### `node` ou `npm` non reconnu

1. Reinstallez Node.js.
2. Fermez et rouvrez le terminal.
3. Refaites `node -v` et `npm -v`.

### Le front s'ouvre mais reste vide

1. Verifiez que `php artisan serve` tourne.
2. Verifiez `VITE_API_BASE_URL` dans `.env`.
3. Relancez `npm run dev`.

### La messagerie ne se met pas a jour

1. Verifiez que `php artisan reverb:start` tourne.
2. Verifiez les variables `VITE_REVERB_*` dans le `.env` frontend.
3. Verifiez les variables `REVERB_*` dans le `.env` backend.

### `php` non reconnu dans terminal

Si vous utilisez XAMPP:

~~~powershell
& "c:\xampp3\php\php.exe" artisan serve
~~~

## 9. Si vous etes bloque

Envoyez ces 3 informations:

1. La commande executee
2. Le message d'erreur complet
3. Le resultat de `node -v`, `npm -v`, `php -v`

Avec ces infos, le probleme est en general vite identifie.