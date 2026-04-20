# Front-end BeneRun (maquette)

Guide complet pour installer, lancer et depanner le front-end Vue.js, meme si vous n'avez pas l'habitude des outils Node.

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

## 2) Prerequis obligatoires

Avant de commencer, installez:

1. Git
2. Node.js (version compatible avec le projet)
3. npm (installe automatiquement avec Node.js)

Version Node requise par le projet:

- `^20.19.0` ou `>=22.12.0`

Verification rapide dans un terminal:

```bash
node -v
npm -v
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

Toutes les commandes front-end doivent etre lancees dans le dossier:

`Front-end/maquette`

Exemple PowerShell (Windows):

```powershell
Set-Location "c:\Users\...\BeneRun\Front-end\maquette"
```

Exemple macOS/Linux:

```bash
cd Front-end/maquette
```

## 5) Configurer le fichier d'environnement

Le projet utilise la variable `VITE_API_BASE_URL` pour savoir ou joindre l'API.

Le fichier `.env` existe deja dans ce dossier. Si besoin, creez-le a partir de `.env.example`:

```bash
cp .env.example .env
```

Contenu attendu (par defaut):

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

Si votre API n'est pas sur `localhost:8000`, adaptez cette URL.

## 6) Installer les dependances

Dans `Front-end/maquette`:

```bash
npm install
```

Alternative recommandee en integration continue (ou installation propre):

```bash
npm ci
```

## 7) Lancer l'application en developpement

Toujours dans `Front-end/maquette`:

```bash
npm run dev
```

Le serveur Vite demarre sur:

- `http://localhost:5173`

Le projet est configure avec un port fixe (`5173`).
Si le port est deja occupe, liberez-le puis relancez la commande.

## 8) Commandes utiles

Depuis `Front-end/maquette`:

```bash
npm run dev      # Lance le serveur de developpement
npm run build    # Genere le build de production (dossier dist/)
npm run preview  # Previsualise localement le build produit
npm run lint     # Lance Oxlint + ESLint (avec auto-fix)
npm run format   # Formate le code avec Prettier
```

## 9) Lien avec le back-end Laravel

Le front peut se lancer seul, mais beaucoup d'ecrans ont besoin de l'API Laravel.

Depuis la racine du projet `BeneRun`:

```bash
php artisan serve
```

API attendue par defaut:

- `http://localhost:8000`

Puis, dans un autre terminal, lancez le front:

```bash
cd Front-end/maquette
npm run dev
```

## 10) Installation complete de zero (copier/coller)

Option A - Vous partez de rien:

```bash
git clone <url-du-repo>
cd BeneRun/Front-end/maquette
cp .env.example .env
npm install
npm run dev
```

Option B - Le repo est deja clone:

```bash
cd BeneRun/Front-end/maquette
npm install
npm run dev
```

Option C - Verifier que le build de production passe:

```bash
cd BeneRun/Front-end/maquette
npm install
npm run build
npm run preview
```

## 11) Depannage rapide

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

### Erreur CORS dans le navigateur

Cause probable: le back-end n'autorise pas l'origine front.

Solution: verifier la configuration CORS Laravel pour autoriser `http://localhost:5173`.

## 12) Rappels importants

- Lancez les commandes front dans `Front-end/maquette`.
- Lancez les commandes Laravel dans la racine `BeneRun`.
- En cas de doute, arretez les serveurs et relancez proprement.

## 13) Arborescence utile (resume)

```text
Front-end/maquette/
  .env
  .env.example
  package.json
  vite.config.js
  src/
  public/
  dist/
```

## 14) Support

Si une etape echoue, partagez:

1. La commande executee
2. Le message d'erreur complet
3. Le resultat de `node -v` et `npm -v`

Avec ces 3 infos, le diagnostic est en general tres rapide.