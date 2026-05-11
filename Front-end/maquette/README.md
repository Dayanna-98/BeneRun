# Front-end BeneRun (maquette)

Guide complet pour installer, lancer et depanner le front-end Vue.js, incluant la nouvelle messagerie (API persistante + WebSocket Reverb).

Ce dossier correspond a l'application front-end situee dans:

`BeneRun/Front-end/maquette`

## 1) A quoi sert ce projet

Ce front-end est une application Vue 3 (Vite) qui communique avec l'API Laravel du projet BeneRun.

Technos principales:

- Vue 3
- Vite
- Vue Router
- Pinia
- Bootstrap 5
- Axios
- Laravel Echo
- Pusher JS (client WebSocket compatible Reverb)

## 2) Prerequis obligatoires

Avant de commencer, installez:

1. Git
2. Node.js (version compatible avec le projet)
3. npm (installe automatiquement avec Node.js)
4. PHP (compatible Laravel 12)
5. Composer

Version Node requise par le projet:

- `^20.19.0` ou `>=22.12.0`

Verification rapide dans un terminal:

```bash
node -v
npm -v
php -v
composer -V
```

Si la version Node est trop ancienne, mettez Node a jour avant d'aller plus loin.

## 3) Recuperer le projet

Si vous n'avez pas encore le code:

```bash
git clone <url-du-repo>
cd BeneRun
```

Si le projet est deja sur votre machine, placez-vous a la racine du repository `BeneRun`.

## 4) Aller dans le bon dossier (important)

Commandes backend Laravel: a lancer dans la racine `BeneRun`.
Commandes frontend Vite: a lancer dans `Front-end/maquette`.

Exemple PowerShell (Windows):

```powershell
Set-Location "c:\Users\...\BeneRun"
Set-Location ".\Front-end\maquette"
```

Exemple macOS/Linux:

```bash
cd BeneRun
cd Front-end/maquette
```

## 5) Configurer le fichier d'environnement front

Le fichier `.env` dans `Front-end/maquette` contient les variables Vite.

Si besoin, creez-le depuis `.env.example`:

```bash
cp .env.example .env
```

Contenu attendu (par defaut):

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=benerun-local-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

## 6) Commandes a faire pour avoir les bonnes dependances

La messagerie a maintenant des dependances frontend ET backend. Faites ces commandes dans cet ordre.

### 6.1 Backend (racine `BeneRun`)

```bash
composer install
php artisan migrate --force
php artisan optimize:clear
```

Si vous etes sur une ancienne branche sans les nouvelles deps lockees, vous pouvez utiliser:

```bash
composer require laravel/sanctum laravel/reverb
```

### 6.2 Frontend (`Front-end/maquette`)

```bash
npm install
```

Si vous etes sur une ancienne branche sans les nouvelles deps lockees, vous pouvez utiliser:

```bash
npm install laravel-echo pusher-js
```

Alternative CI (install propre et reproductible):

```bash
npm ci
```

## 7) Lancer l'application en developpement (messagerie incluse)

La messagerie temps reel a besoin de 3 processus.

### Terminal 1 - API Laravel (racine `BeneRun`)

```bash
php artisan serve
```

### Terminal 2 - Serveur WebSocket (racine `BeneRun`)

```bash
php artisan reverb:start
```

### Terminal 3 - Frontend (`Front-end/maquette`)

```bash
npm run dev
```

URL front par defaut:

- `http://localhost:5173`

## 8) Commandes utiles

Depuis `Front-end/maquette`:

```bash
npm run dev      # Lance le serveur de developpement
npm run build    # Genere le build de production (dossier dist/)
npm run preview  # Previsualise localement le build produit
npm run lint     # Lance Oxlint + ESLint (avec auto-fix)
npm run format   # Formate le code avec Prettier
```

Depuis la racine `BeneRun`:

```bash
php artisan serve
php artisan reverb:start
php artisan migrate
php artisan optimize:clear
```

## 9) Installation complete de zero (copier/coller)

Option A - Vous partez de rien:

```bash
git clone <url-du-repo>
cd BeneRun
composer install
php artisan migrate --force
php artisan optimize:clear

cd Front-end/maquette
cp .env.example .env
npm install
```

Puis lancez:

```bash
# terminal 1
cd BeneRun
php artisan serve

# terminal 2
cd BeneRun
php artisan reverb:start

# terminal 3
cd BeneRun/Front-end/maquette
npm run dev
```

Option B - Le repo est deja clone:

```bash
cd BeneRun
composer install
php artisan migrate --force
php artisan optimize:clear

cd Front-end/maquette
npm install
```

Option C - Verifier que le build de production passe:

```bash
cd BeneRun/Front-end/maquette
npm ci
npm run build
npm run preview
```

## 10) Depannage rapide

### Erreur: `node` ou `npm` non reconnu

Cause probable: Node.js non installe ou PATH non recharge.

Solution:

1. Installer/reinstaller Node.js.
2. Fermer puis rouvrir le terminal.
3. Refaire `node -v` et `npm -v`.

### Erreur au `npm install`

Cause probable: version Node incompatible.

Solution:

1. Verifier `node -v`.
2. Installer une version compatible (`20.19+` ou `22.12+`).
3. Relancer `npm install`.

### Le front demarre mais rien ne charge

Cause probable: API Laravel non demarree ou mauvaise URL API.

Solution:

1. Lancer `php artisan serve` depuis la racine `BeneRun`.
2. Verifier `VITE_API_BASE_URL` dans `.env`.
3. Redemarrer le front (`npm run dev`).

### La messagerie ne se met pas a jour en direct

Cause probable: Reverb non lance ou variable WebSocket incorrecte.

Solution:

1. Lancer `php artisan reverb:start`.
2. Verifier `VITE_REVERB_*` dans `Front-end/maquette/.env`.
3. Verifier `BROADCAST_CONNECTION=reverb` et `REVERB_*` dans `BeneRun/.env`.
4. Lancer `php artisan optimize:clear` puis redemarrer les 3 processus.

### Erreur SQL `chat_conversations` inexistante

Cause probable: migration chat non appliquee.

Solution:

```bash
cd BeneRun
php artisan migrate --force
```

### Erreur 401/403 sur `/broadcasting/auth`

Cause probable: utilisateur non authentifie ou token invalide.

Solution:

1. Se reconnecter dans l'app.
2. Verifier que le token est present dans le storage.
3. Verifier que l'utilisateur est bien membre de la conversation privee cible.

## 11) Rappels importants

- Lancez les commandes front dans `Front-end/maquette`.
- Lancez les commandes Laravel dans la racine `BeneRun`.
- Pour la messagerie temps reel, `php artisan reverb:start` est obligatoire.

## 12) Arborescence utile (resume)

```text
Front-end/maquette/
  .env
  .env.example
  package.json
  vite.config.js
  src/services/chatApiService.js
  src/services/realtime.js
  src/views/Messaging.vue
  src/
  public/
  dist/
```

## 13) Support

Si une etape echoue, partagez:

1. La commande executee
2. Le message d'erreur complet
3. Le resultat de `node -v`, `npm -v`, `php -v`

Avec ces infos, le diagnostic est generalement rapide.