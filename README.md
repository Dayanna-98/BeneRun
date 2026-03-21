# BeneRun
Application de gestion des bénévoles pour les courses de l'association Béné'Run.
L'installation se fait en deux étapes : 
    l'installation de l'environnement du back-end et du front-end.
    
# Démarches à suivre pour l'installation du back-end du projet en local
Avant toute chose, pour l'installation et l'utilisation du projet il est nécessaire de rejoindre le repository sur GitHub et d'avoir sur son poste : 
    - Un compte GitHub pour rejoindre le repository GitHub
    - Visual Studio Code
    - XAMPP ou WAMPP
    - POSTMAN

1. Cloner le repository en copiant l'URL. Dans un invite de commande ou le terminal de Visual Studio Code se placer à l'emplacement où l'on souhaite cloner le projet et exécuter la commande : 
    git clone https://github.com/Dayanna-98/BeneRun.git

2. Après le clonage, ouvrir le projet et se placer à la racine du projet. Dans un invite de commande ou le terminal de Visual Studio Code exécuter la commande : 
    cd BeneRun

3. Installer les dépendances du projet Composer dans un invite de commande ou le terminal de Visual Studio Code en exécutant la commande :
    composer install

4. Copier le fichier .env dans un invite de commande ou le terminal de Visual Studio Code en exécutant la commande :
    cp .env.example .env

5. Générer la clé Laravel dans un invite de commande ou le terminal de Visual Studio Code en exécutant la commande :
    php artisan key:generate

6. Dans le fichier .env :
    Tout en haut à la ligne : APP_KEY= insérer votre votre key générée
    et dans la partie connexion à la BDD mettre : 

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=le port sur quel notre XAMPP OU WAMPP tourne
    DB_DATABASE=benerun
    DB_USERNAME=root
    DB_PASSWORD=

Cette partie est à configurer selon les paramètres et configuration de chacun. La DB_CONNECTION et DB_DATABASE doivent toutefois être comme indiquées ci-dessus. 

7. Créer la BDD en local dans PHPMyAdmin dans un invite de commande ou le terminal de VS Code en exécutant la commande :
    php artisan migrate    

8. Lancer le serveur Laravel dans un invite de commande ou le terminal de VS Code en exécutant la commande : 
    php artisan serve

9. Vérifier que l'application est accessible en cliquant sur l'URL indiquée.

Documentation API (Scramble) L’API est documentée automatiquement grâce à Scramble (dedoc/scramble), une alternative moderne à Swagger permettant une génération plus stable et compatible avec Laravel. 📌 Accès à la documentation : http://localhost:8000/docs/api 
📌 Régénérer la documentation : php artisan scramble:analyze

Scramble fournit automatiquement :

la liste des endpoints les paramètres attendus les réponses JSON les schémas des modèles une interface interactive

Marche à suivre pour l'installation du front-end :

🏃 Maquette — Running Geneva Application mobile-first développée avec Vue.js 3, Bootstrap 5, Vue Router et Pinia. Interface de gestion de bénévoles pour événements sportifs (Running Geneva).

📋 Prérequis Node.js v18 ou supérieur npm v9 ou supérieur Un éditeur de code (recommandé : VS Code) 🚀 Installation

Cloner le projet git clone https://github.com/votre-repo/maquette.git
Se placer dans le dossier "maquette" cd maquette

Installer les dépendances npm install

Lancer le serveur de développement npm run dev

L'application sera disponible sur http://localhost:5173

📦 Dépendances principales Package Version Rôle vue ^3.x Framework principal vue-router ^4.x Gestion des routes pinia ^2.x Gestion d'état (store) axios ^1.x Requêtes HTTP vers l'API Laravel bootstrap ^5.x UI / styles lucide-vue-next latest Icônes Installer manuellement si besoin : npm install vue-router pinia axios bootstrap lucide-vue-next

🗂️ Structure du projet src/ ├── assets/ │ ├── css/ │ │ ├── index.css # CSS global (importe fonts + theme) │ │ └── theme.css # Variables et surcharges Bootstrap │ ├── fonts/ │ │ └── fonts.css # Déclarations @font-face │ └── logo.png ├── components/ │ ├── figma/ # Composants issus de la maquette Figma │ └── ui/ # Composants UI réutilisables (Button, Card, etc.) ├── composables/ # Logique réutilisable (useAuth, etc.) ├── data/ │ └── mockData.js # Données fictives pour le développement ├── router/ │ └── index.js # Définition des routes + navigation guards ├── services/ │ └── api.js # Instance Axios + intercepteurs ├── stores/ # Stores Pinia ├── utils/ │ └── auth.js # Helpers d'authentification ├── views/ # Pages (une par route) │ ├── Login.vue │ ├── Profile.vue │ └── ... ├── App.vue └── main.js

⚙️ Configuration src/main.js import { createApp } from 'vue' import { createPinia } from 'pinia' import router from './router' import App from './App.vue'

import 'bootstrap/dist/css/bootstrap.min.css' import 'bootstrap/dist/js/bootstrap.bundle.min.js' import './assets/css/index.css'

createApp(App).use(createPinia()).use(router).mount('#app')

src/assets/css/index.css @import '../fonts/fonts.css'; @import './theme.css';

src/services/api.js

Configure l'URL de base de l'API Laravel :

import axios from 'axios'

const api = axios.create({ baseURL: 'http://localhost:8000/api', // ← adapter selon votre environnement headers: { 'Content-Type': 'application/json' } })

// Ajout automatique du token JWT api.interceptors.request.use(config => { const token = localStorage.getItem('token') if (token) config.headers.Authorization = Bearer ${token} return config })

// Redirection si token expiré api.interceptors.response.use( res => res, err => { if (err.response?.status === 401) { localStorage.removeItem('token') window.location.href = '/login' } return Promise.reject(err) } )

export default api

🔐 Authentification

L'application utilise JWT (token Bearer) stocké dans localStorage.

Le token est ajouté automatiquement à chaque requête via l'intercepteur Axios Les routes protégées utilisent meta: { requiresAuth: true } dans le router En cas de 401, l'utilisateur est redirigé vers /login

⚠️ Le backend Laravel doit exposer une API REST avec authentification JWT (ex: laravel/sanctum en mode API token ou tymon/jwt-auth).

🛣️ Routes principales Chemin Vue Protégée /login Login.vue Non /profile Profile.vue Oui /missions Missions.vue Oui /events Events.vue Oui 🏗️ Build pour la production npm run build

Les fichiers compilés seront dans le dossier dist/.

Pour prévisualiser le build :

npm run preview

🔧 Commandes utiles npm run dev # Démarrer en développement npm run build # Build production npm run preview # Prévisualiser le build

🌐 Connexion au backend Laravel Démarrer le serveur Laravel : php artisan serve S'assurer que CORS est configuré dans config/cors.php pour autoriser http://localhost:5173 Adapter baseURL dans src/services/api.js 📝 Notes de développement Les données dans src/data/mockData.js sont fictives — à remplacer par les appels API réels Les composants dans src/components/ui/ sont des adaptations Bootstrap des composants Radix UI originaux Le projet utilise la Composition API de Vue 3 avec <script setup> Pas de TypeScript — le projet utilise JavaScript pur 👥 Auteurs Projet initié à partir d'une maquette Figma (React/TSX) Converti en Vue.js 3 + Bootstrap 5

>>>>>>> 1bce67529fda73e709b46926ff0654232e6cdb5e
