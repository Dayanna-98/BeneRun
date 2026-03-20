Voici le README complet en markdown — à placer à la racine du projet sous le nom README.md :

# 🏃 Maquette — Running Geneva

Application mobile-first développée avec **Vue.js 3**, **Bootstrap 5**, **Vue Router** et **Pinia**.  
Interface de gestion de bénévoles pour événements sportifs (Running Geneva).

---

## 📋 Prérequis

- [Node.js](https://nodejs.org/) v18 ou supérieur
- [npm](https://www.npmjs.com/) v9 ou supérieur
- Un éditeur de code (recommandé : [VS Code](https://code.visualstudio.com/))

---

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/votre-repo/maquette.git
cd maquette

2. Installer les dépendances
npm install

3. Lancer le serveur de développement
npm run dev


L'application sera disponible sur http://localhost:5173

📦 Dépendances principales
Package	Version	Rôle
vue	^3.x	Framework principal
vue-router	^4.x	Gestion des routes
pinia	^2.x	Gestion d'état (store)
axios	^1.x	Requêtes HTTP vers l'API Laravel
bootstrap	^5.x	UI / styles
lucide-vue-next	latest	Icônes
Installer manuellement si besoin :
npm install vue-router pinia axios bootstrap lucide-vue-next

🗂️ Structure du projet
src/
├── assets/
│   ├── css/
│   │   ├── index.css       # CSS global (importe fonts + theme)
│   │   └── theme.css       # Variables et surcharges Bootstrap
│   ├── fonts/
│   │   └── fonts.css       # Déclarations @font-face
│   └── logo.png
├── components/
│   ├── figma/              # Composants issus de la maquette Figma
│   └── ui/                 # Composants UI réutilisables (Button, Card, etc.)
├── composables/            # Logique réutilisable (useAuth, etc.)
├── data/
│   └── mockData.js         # Données fictives pour le développement
├── router/
│   └── index.js            # Définition des routes + navigation guards
├── services/
│   └── api.js              # Instance Axios + intercepteurs
├── stores/                 # Stores Pinia
├── utils/
│   └── auth.js             # Helpers d'authentification
├── views/                  # Pages (une par route)
│   ├── Login.vue
│   ├── Profile.vue
│   └── ...
├── App.vue
└── main.js

⚙️ Configuration
src/main.js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import './assets/css/index.css'

createApp(App).use(createPinia()).use(router).mount('#app')

src/assets/css/index.css
@import '../fonts/fonts.css';
@import './theme.css';

src/services/api.js

Configure l'URL de base de l'API Laravel :

import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api', // ← adapter selon votre environnement
  headers: { 'Content-Type': 'application/json' }
})

// Ajout automatique du token JWT
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Redirection si token expiré
api.interceptors.response.use(
  res => res,
  err => {
    if (err.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

export default api

🔐 Authentification

L'application utilise JWT (token Bearer) stocké dans localStorage.

Le token est ajouté automatiquement à chaque requête via l'intercepteur Axios
Les routes protégées utilisent meta: { requiresAuth: true } dans le router
En cas de 401, l'utilisateur est redirigé vers /login

⚠️ Le backend Laravel doit exposer une API REST avec authentification JWT (ex: laravel/sanctum en mode API token ou tymon/jwt-auth).

🛣️ Routes principales
Chemin	Vue	Protégée
/login	Login.vue	Non
/profile	Profile.vue	Oui
/missions	Missions.vue	Oui
/events	Events.vue	Oui
🏗️ Build pour la production
npm run build


Les fichiers compilés seront dans le dossier dist/.

Pour prévisualiser le build :

npm run preview

🔧 Commandes utiles
npm run dev        # Démarrer en développement
npm run build      # Build production
npm run preview    # Prévisualiser le build

🌐 Connexion au backend Laravel
Démarrer le serveur Laravel : php artisan serve
S'assurer que CORS est configuré dans config/cors.php pour autoriser http://localhost:5173
Adapter baseURL dans src/services/api.js
📝 Notes de développement
Les données dans src/data/mockData.js sont fictives — à remplacer par les appels API réels
Les composants dans src/components/ui/ sont des adaptations Bootstrap des composants Radix UI originaux
Le projet utilise la Composition API de Vue 3 avec <script setup>
Pas de TypeScript — le projet utilise JavaScript pur
👥 Auteurs
Projet initié à partir d'une maquette Figma (React/TSX)
Converti en Vue.js 3 + Bootstrap 5

Copie ce contenu dans un fichier `README.md` à la racine du projet (au même niveau que `package.json`).
