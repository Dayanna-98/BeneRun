# Dossier source - Technologies BeneRun

Date de generation: 2026-05-28

Objectif: fournir un inventaire technique complet et verifie des technologies du projet BeneRun, avec leur role, leur emplacement dans le code et leur interet.

Regle de fiabilite: uniquement des technologies observees dans les fichiers du depot (dependances, imports, config, workflows).

## 1) Sources de verite utilisees

- composer.json
- package.json (racine backend)
- Front-end/maquette/package.json
- vite.config.js (racine)
- Front-end/maquette/vite.config.js
- Front-end/maquette/src/main.js
- Front-end/maquette/src/services/api.js
- Front-end/maquette/src/services/realtime.js
- bootstrap/app.php
- routes/channels.php
- config/broadcasting.php
- config/scramble.php
- phpunit.xml
- phpstan.neon
- .github/workflows/tests.yml
- .github/workflows/quality.yml
- .github/workflows/deploy-optional.yml
- scripts/install-linting-tools.sh
- scripts/pre-commit
- resources/css/app.css
- resources/js/app.js
- resources/js/bootstrap.js

## 2) Pile principale (runtime)

## 2.1 Backend

- PHP 8.2+
  - Source: composer.json require php ^8.2
  - Role: langage d execution backend

- Laravel Framework 12
  - Source: composer.json require laravel/framework ^12.0
  - Role: framework web/API (routing, controllers, ORM, middleware, migrations)

- Laravel Sanctum
  - Source: composer.json require laravel/sanctum ^4.3
  - Preuves usage:
    - auth:sanctum dans routes API
    - request->user('sanctum') dans controllers
    - HasApiTokens dans app/Models/User.php
    - createToken dans UserController@login
  - Role: authentification API token

- Laravel Reverb
  - Source: composer.json require laravel/reverb ^1.10
  - Preuves usage:
    - BROADCAST_CONNECTION=reverb dans .env.example
    - config/broadcasting.php connexion reverb
    - bootstrap/app.php withBroadcasting + middleware auth:sanctum
    - channels prives dans routes/channels.php
    - events broadcastes app/Events/ChatMessageSent.php et ChatConversationChanged.php
  - Role: WebSocket / temps reel (messagerie)

- dedoc/scramble
  - Source: composer.json require dedoc/scramble ^0.13.16
  - Preuves usage:
    - config/scramble.php
    - README mention /docs/api#/
  - Role: generation doc OpenAPI + interface docs

- Eloquent ORM (inclus Laravel)
  - Preuves usage:
    - modele app/Models/*.php
    - migrations database/migrations
  - Role: mapping objets vers tables SQL

## 2.2 Frontend principal

- Vue 3
  - Source: Front-end/maquette/package.json dependency vue
  - Preuves usage:
    - createApp dans src/main.js
    - composants .vue dans src/views et src/components

- Vue Router
  - Source: dependency vue-router
  - Preuves usage:
    - src/router/index.js createRouter/createWebHistory
    - imports useRouter/useRoute dans nombreuses vues
  - Role: navigation SPA

- Pinia
  - Source: dependency pinia
  - Preuves usage:
    - createPinia dans src/main.js
    - defineStore dans src/stores/counter.js
  - Role: gestion d etat frontend

- Axios
  - Source: dependency axios
  - Preuves usage:
    - instance API centrale src/services/api.js
    - imports additionnels UserMap/UserLocation
  - Role: client HTTP API

- laravel-echo + pusher-js
  - Source: dependencies laravel-echo, pusher-js
  - Preuves usage:
    - src/services/realtime.js (Echo + Pusher)
    - authEndpoint /broadcasting/auth
  - Role: client temps reel WebSocket vers Reverb

- Bootstrap 5 + @popperjs/core
  - Source: dependencies bootstrap, @popperjs/core
  - Preuves usage:
    - imports CSS/JS bootstrap dans src/main.js
    - @popperjs/core est requis par composants Bootstrap (usage indirect)
  - Role: base UI et composants interactifs

- Lucide Vue
  - Source: dependency lucide-vue-next
  - Preuves usage:
    - imports dans App.vue, vues, composants UI
  - Role: bibliotheque d icones

- Leaflet
  - Source: dependency leaflet
  - Preuves usage:
    - import L from leaflet dans src/views/UserMap.vue
  - Role: cartes et geolocalisation

- Chart.js + vue-chartjs
  - Source: dependencies chart.js, vue-chartjs
  - Preuves usage:
    - src/components/ui/Chart.vue
  - Role: graphiques statistiques

- jsPDF + xlsx
  - Source: dependencies jspdf, xlsx
  - Preuves usage:
    - imports dans src/views/Statistics.vue
  - Role: export PDF/Excel des donnees stats

- vee-validate + yup
  - Source: dependencies vee-validate, yup
  - Preuves usage:
    - src/components/ui/Form.vue
    - src/components/ui/FormField.vue
  - Role: validation de formulaires

- vue-toastification
  - Source: dependency vue-toastification
  - Preuves usage:
    - plugin installe dans src/main.js
    - composable useToast dans src/composables/useToast.js
  - Role: notifications utilisateur

## 2.3 Frontend Laravel (racine)

- Vite + laravel-vite-plugin
  - Source: package.json racine + vite.config.js racine
  - Preuves usage:
    - build assets resources/css/app.css et resources/js/app.js
  - Role: build des assets frontend cote Laravel

- Tailwind CSS 4 (couche racine)
  - Source: package.json racine + vite plugin tailwind + resources/css/app.css
  - Preuves usage:
    - @import tailwindcss dans resources/css/app.css
  - Role: utilitaires CSS sur couche Laravel

## 3) Base de donnees

- MySQL (runtime local)
  - Source: .env.example DB_CONNECTION=mysql
  - Role: persistence de production/dev local

- SQLite in-memory (tests backend)
  - Source: phpunit.xml DB_CONNECTION=sqlite, DB_DATABASE=:memory:
  - Role: execution rapide et isolee des tests

## 4) Qualite, tests, CI/CD

## 4.1 Backend QA

- PHPUnit
  - Source: composer require-dev phpunit/phpunit
  - Preuves usage:
    - phpunit.xml
    - tests/Feature, tests/Unit
    - workflow tests.yml

- Laravel Pint
  - Source: composer require-dev laravel/pint
  - Preuves usage:
    - workflow tests.yml run ./vendor/bin/pint --test
    - script pre-commit

- PHPStan (niveau 5)
  - Source: phpstan.neon
  - Preuves usage:
    - workflow quality.yml execute phpstan analyse

- Larastan / extensions PHPStan (optionnel local)
  - Source: scripts/install-linting-tools.sh
  - Role: analyse statique enrichie Laravel

- Faker / Mockery / Collision
  - Source: composer require-dev
  - Role:
    - Faker: donnees fake tests/seed
    - Mockery: mocks doubles test
    - Collision: affichage erreurs CLI dev/tests

## 4.2 Frontend QA

- Vitest
  - Source: Front-end/maquette devDependency vitest
  - Preuves usage:
    - scripts npm test, test:coverage
    - specs dans src/tests

- @vue/test-utils
  - Source: devDependency
  - Preuves usage:
    - imports mount/flushPromises dans specs

- jsdom
  - Source: devDependency
  - Preuves usage:
    - vite.config test.environment jsdom

- ESLint + eslint-plugin-vue + @eslint/js
  - Source: devDependencies
  - Preuves usage:
    - Front-end/maquette/eslint.config.js

- Oxlint + eslint-plugin-oxlint
  - Source: devDependencies
  - Preuves usage:
    - .oxlintrc.json
    - script lint:oxlint
    - workflow quality.yml

- Prettier
  - Source: devDependency
  - Preuves usage:
    - script npm format

- npm-run-all2
  - Source: devDependency
  - Preuves usage:
    - script lint compose lint:*

## 4.3 CI/CD

- GitHub Actions
  - Source: .github/workflows/*.yml
  - Workflows:
    - tests.yml: tests backend matrix PHP 8.2/8.3, build frontends, tests frontend
    - quality.yml: phpstan, audit composer, lint frontend
    - deploy-optional.yml: trame de deploiement manuel/auto

- Codecov (optionnel)
  - Source: steps upload coverage conditionnels sur CODECOV_TOKEN
  - Role: centraliser la couverture de tests

- Composer audit
  - Source: quality.yml
  - Role: detection vulnerabilites dependances PHP

## 5) Outils dev additionnels

- Laravel Tinker
  - Source: composer require laravel/tinker
  - Role: REPL PHP/Laravel pour debug rapide

- Laravel Pail
  - Source: composer require-dev laravel/pail
  - Role: suivi logs en dev (script composer dev)

- Laravel Sail
  - Source: composer require-dev laravel/sail
  - Role: environnement Docker Laravel (present, usage non impose dans docs locales)

- Concurrently
  - Source: package.json racine
  - Preuves usage:
    - script composer dev lance serve + queue + pail + vite en parallele
  - Role: lancer plusieurs services en une commande

## 6) Notes de cadrage importantes

- Deux pipelines frontend coexistent:
  - pipeline assets Laravel racine (resources/*, tailwind racine)
  - frontend SPA principal dans Front-end/maquette (Vue 3)

- Certaines technos sont runtime critiques (Laravel, Vue, Sanctum, Reverb, MySQL).
- D autres sont qualite/outillage (PHPStan, Pint, ESLint, Oxlint, workflows CI).
- D autres sont fonctionnelles metier/UI (Leaflet, Chart.js, jsPDF, xlsx, vee-validate, lucide).

Ce document sert de base factuelle pour produire une cartographie techno explicative complete.
