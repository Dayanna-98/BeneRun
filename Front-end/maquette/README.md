# Front-end BeneRun (maquette)

Ce document explique, pas a pas, comment faire tourner **tout le projet** apres un `git pull`:

- backend Laravel (API)
- base de donnees MySQL/MariaDB
- websocket Reverb
- frontend Vue/Vite

Chemin de ce front:

`BeneRun/Front-end/maquette`

## 0) Cas de figure (choisissez votre cas)

### Cas A - Vous recuperez le projet de zero (nouveau PC / nouveau clone)

1. Cloner le repo.
2. Installer les dependances backend + frontend.
3. Creer les `.env`.
4. Lancer les migrations.
5. Demarrer les 3 terminaux (API, Reverb, Vite).

Commandes:

```bash
git clone <url-du-repo>
cd BeneRun
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan optimize:clear

cd Front-end/maquette
npm install
cp .env.example .env
```

### Cas B - Vous avez deja le projet, vous faites juste un pull (cas le plus frequent)

Commandes minimales a faire apres le pull:

```bash
cd BeneRun
git pull
composer install
php artisan migrate --force
php artisan optimize:clear

cd Front-end/maquette
npm install
```

### Cas C - Erreurs de librairies manquantes (`Class not found`, `Cannot find module`, etc.)

1. D'abord faire `composer install` et `npm install`.
2. Si l'erreur persiste, utiliser les commandes de la section 2.4 (reinstallation des libs utilisees).

## 1) Prerequis (a installer une seule fois)

Installez ces outils sur votre machine:

1. Git
2. PHP (compatible Laravel 12)
3. Composer
4. Node.js (version requise: `^20.19.0` ou `>=22.12.0`)
5. npm (installe avec Node)
6. MySQL ou MariaDB

Verifiez rapidement:

```bash
git --version
php -v
composer -V
node -v
npm -v
```

Si `node -v` ne respecte pas la version requise, mettez Node a jour avant de continuer.

## 2) Apres un git pull: commandes obligatoires

Ces commandes sont a faire **a chaque fois qu'il y a des changements de dependances ou de migrations**.

### 2.1 Dans la racine du projet `BeneRun` (backend)

```bash
composer install
```

### 2.2 Dans `Front-end/maquette` (frontend)

```bash
npm install
```

### 2.3 Commandes d'ajout de librairie a eviter (sauf si vous etes la personne qui code la feature)

Si vous recupererez juste la derniere version du projet, **n'ajoutez pas de nouvelles librairies**.

N'utilisez pas ces commandes apres un simple pull:

```bash
composer require <package>
composer remove <package>
npm install <package>
npm install -D <package>
npm uninstall <package>
npm update
```

Pourquoi c'est risqué:

1. Ca modifie `composer.json`, `composer.lock`, `package.json`, `package-lock.json`.
2. Vous pouvez installer des versions differentes de celles attendues par l'equipe.
3. Vous risquez des conflits Git inutiles et des bugs "chez moi ca marche".

Ce qu'il faut faire a la place:

1. `composer install` (backend)
2. `npm install` ou `npm ci` (frontend)

En bref: **installer ce qui est deja defini dans les lock files**, ne pas changer les dependances.

### 2.4 Si vraiment des libs manquent: commandes de reinstallation des technos utilisees

Normalement, `composer install` et `npm install` suffisent.

Utilisez ce bloc seulement si vous avez une erreur explicite de package manquant.

#### Backend Laravel (dans `BeneRun`)

```bash
composer require laravel/reverb laravel/sanctum dedoc/scramble laravel/tinker
```

#### Frontend runtime (dans `Front-end/maquette`)

```bash
npm install vue vue-router pinia axios bootstrap @popperjs/core leaflet laravel-echo pusher-js chart.js vue-chartjs lucide-vue-next vee-validate yup vue-toastification
```

#### Frontend dev tools (dans `Front-end/maquette`)

```bash
npm install -D vite @vitejs/plugin-vue vite-plugin-vue-devtools eslint @eslint/js eslint-config-prettier eslint-plugin-vue eslint-plugin-oxlint oxlint prettier globals npm-run-all2
```

Puis relancez:

```bash
cd BeneRun
composer install
php artisan optimize:clear

cd Front-end/maquette
npm install
```

## 3) Configuration backend (`BeneRun/.env`)

Placez-vous dans `BeneRun`.

Si le fichier `.env` n'existe pas:

```bash
cp .env.example .env
```

Puis generez la cle Laravel:

```bash
php artisan key:generate
```

### 3.1 Variables minimum a verifier

Dans `BeneRun/.env`, verifiez au minimum:

```env
APP_URL=http://127.0.0.1:8000

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
```

Adaptez `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` a votre machine.

## 4) Configuration frontend (`Front-end/maquette/.env`)

Placez-vous dans `Front-end/maquette`.

Si `.env` n'existe pas:

```bash
cp .env.example .env
```

Valeurs a verifier:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
VITE_REVERB_APP_KEY=benerun-local-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

Important: `VITE_REVERB_APP_KEY` doit correspondre a `REVERB_APP_KEY` du backend.

## 5) Base de donnees: creation + migrations

1. Assurez-vous que MySQL/MariaDB tourne.
2. Creez la base `benerun` (ou le nom mis dans votre `.env`).
3. Dans `BeneRun`, lancez:

```bash
php artisan migrate --force
php artisan optimize:clear
```

Si votre serveur MySQL/MariaDB est arrete, l'API renverra souvent des erreurs 500.

### 5.1 Tres important apres un `git pull`: ajouter les nouvelles tables

Si un collegue a ajoute une migration (nouvelle table/colonne), votre base locale ne se met pas a jour toute seule.

Vous devez lancer cette commande dans `BeneRun`:

```bash
php artisan migrate --force
```

Pour verifier s'il reste des migrations en attente:

```bash
php artisan migrate:status
```

Si des lignes sont en `Pending`, relancez:

```bash
php artisan migrate --force
```

## 6) Demarrage complet (3 terminaux a allumer)

La messagerie en temps reel necessite 3 processus en meme temps.

### Terminal A - API Laravel (dans `BeneRun`)

```bash
php artisan serve
```

### Terminal B - Reverb WebSocket (dans `BeneRun`)

```bash
php artisan reverb:start
```

### Terminal C - Frontend Vite (dans `Front-end/maquette`)

```bash
npm run dev
```

URL front par defaut: `http://localhost:5173`

Resume ultra court des terminaux:

1. Terminal A (dossier `BeneRun`): `php artisan serve`
2. Terminal B (dossier `BeneRun`): `php artisan reverb:start`
3. Terminal C (dossier `Front-end/maquette`): `npm run dev`

## 7) Test rapide pour savoir si tout tourne

1. Ouvrez `http://localhost:5173`
2. Connectez-vous
3. Verifiez qu'une page charge des donnees API (missions, dashboard, etc.)
4. Ouvrez la messagerie et verifiez qu'elle affiche les conversations

Si ces 4 points passent, la stack front + back est OK.

## 8) Procedure simple a copier-coller (Windows PowerShell)

```powershell
# 1) Aller dans le repo
Set-Location "C:\Users\<votre_user>\...\BeneRun"

# 2) Installer backend
composer install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate

# 3) Migrer + nettoyer cache
php artisan migrate --force
php artisan optimize:clear

# 4) Installer frontend
Set-Location ".\Front-end\maquette"
npm install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
```

Ensuite, lancez les 3 terminaux de la section 6.

## 9) Erreurs frequentes et correction

### Erreur `SQLSTATE` / API en 500 partout

Cause probable: DB arretee ou mauvais identifiants DB.

Correction:

1. Demarrer MySQL/MariaDB.
2. Verifier `DB_*` dans `BeneRun/.env`.
3. Relancer `php artisan migrate --force`.

### Front charge mais aucune donnee

Cause probable: API non demarree ou `VITE_API_BASE_URL` incorrect.

Correction:

1. Lancer `php artisan serve`.
2. Verifier `VITE_API_BASE_URL=http://127.0.0.1:8000/api`.
3. Redemarrer `npm run dev`.

### Messagerie pas en temps reel

Cause probable: Reverb non demarre ou variables Reverb incoherentes.

Correction:

1. Lancer `php artisan reverb:start`.
2. Verifier les variables `REVERB_*` (backend) et `VITE_REVERB_*` (frontend).
3. Faire `php artisan optimize:clear` puis redemarrer les 3 terminaux.

### Erreur `node` ou `npm` non reconnu

Cause probable: Node absent ou PATH non recharge.

Correction:

1. Installer/reinstaller Node.
2. Fermer et rouvrir le terminal.
3. Relancer `node -v` puis `npm -v`.

## 10) Commandes utiles

Dans `BeneRun`:

```bash
php artisan serve
php artisan reverb:start
php artisan migrate
php artisan optimize:clear
php artisan test
```

Dans `Front-end/maquette`:

```bash
npm run dev
npm run build
npm run preview
npm run lint
npm run format
```

## 11) Si ca ne marche toujours pas

Partagez ces 4 infos pour un diagnostic rapide:

1. Commande executee
2. Erreur complete (copie totale)
3. Resultat de `node -v`, `npm -v`, `php -v`
4. Contenu (sans secret) de `BeneRun/.env` pour `APP_URL`, `DB_*`, `BROADCAST_CONNECTION`, `REVERB_*` et de `Front-end/maquette/.env` pour `VITE_*`