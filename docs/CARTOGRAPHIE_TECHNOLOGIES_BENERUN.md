# Cartographie des technologies BeneRun

Version: 2026-05-28
Public vise: equipe projet, onboarding, redaction technique

Objectif: expliquer toutes les technologies utilisees dans BeneRun, leur role, ou elles servent, pourquoi elles ont ete choisies et leurs avantages.

## 1) Vue d ensemble rapide

BeneRun repose sur deux couches applicatives:

- Backend API Laravel (auth, metier, base de donnees, temps reel, documentation API)
- Frontend SPA Vue (interface utilisateur, navigation, etat, API, cartes, stats)

Autour de ces couches, le projet ajoute:

- une couche qualite (tests, lint, analyse statique)
- une couche CI/CD (GitHub Actions, audit, couverture)

## 2) Technologies backend

| Technologie | Role | Ou elle sert | Pourquoi ce choix | Avantages |
|---|---|---|---|---|
| PHP 8.2+ | Langage backend | Toute la couche Laravel | Standard ecosysteme Laravel moderne | Performance, ecosysteme mature, large base de bibliotheques |
| Laravel 12 | Framework web/API | app/, routes/, config/, database/ | Structure MVC solide et productive | Routing, middleware, ORM, migrations, tests integres |
| Eloquent ORM | Acces donnees objet | app/Models, controllers, services | Evite SQL verbeux pour le CRUD courant | Productivite, relations expressives, maintenance facilitee |
| Laravel Sanctum | Authentification API token | routes/api.php, User model, UserController | Adaptation naturelle aux SPA | Tokens simples, integration Laravel native |
| Laravel Reverb | Temps reel WebSocket | config/broadcasting.php, app/Events, routes/channels.php | Besoin de messagerie quasi temps reel | Latence faible, architecture event-driven, stack Laravel coherente |
| dedoc/scramble | Doc OpenAPI auto | config/scramble.php, /docs/api | Garder une API documentee sans doc manuelle lourde | Documentation synchronisee avec le code |
| MySQL | Base runtime | .env backend + migrations | Base relationnelle robuste pour metier benevoles/evenements | Fiable, connue des equipes, bonne perf transactionnelle |
| SQLite in-memory | Base de tests | phpunit.xml | Tests rapides et isoles en CI | Execution rapide, pas de service externe requis |
| Rate Limiter Laravel | Protection anti abus | AppServiceProvider + middlewares throttle | Limiter les tentatives login/reset/actions sensibles | Renforce la securite API |
| SecurityHeaders middleware | Headers securite HTTP | app/Http/Middleware/SecurityHeaders.php, bootstrap/app.php | Durcir les reponses HTTP par defaut | Reduit XSS/clickjacking et fuites de contexte |

## 3) Technologies frontend (SPA principale)

| Technologie | Role | Ou elle sert | Pourquoi ce choix | Avantages |
|---|---|---|---|---|
| Vue 3 | Framework UI | Front-end/maquette/src | SPA moderne reactive | Composants clairs, DX elevee, bonne maintenabilite |
| Vue Router | Navigation SPA | src/router/index.js + vues | Multiples ecrans metier avec guard auth/roles | Routing declaratif, guards centralises |
| Pinia | Etat global frontend | src/main.js, src/stores | Partage d etat entre composants | Simple, officiel Vue 3, testable |
| Axios | Client HTTP | src/services/api.js + services metier | Uniformiser appels API et gestion erreurs/auth | Interceptors, timeouts, centralisation |
| laravel-echo | Client events temps reel | src/services/realtime.js | Consommer les events broadcast Laravel/Reverb | API de subscription claire |
| pusher-js | Transport WebSocket pour Echo | src/services/realtime.js | Necessaire au mode broadcaster reverb/echo | Stable, robuste, largement utilise |
| Bootstrap 5 | Base UI/CSS | src/main.js (import css/js) | Acceleration UI pour composants standards | Grille, composants prets, bonne compatibilite |
| @popperjs/core | Positionnement popovers/dropdowns | usage indirect via Bootstrap | Dependance officielle Bootstrap | Positionnement fiable overlays/menus |
| lucide-vue-next | Icones UI | nombreuses vues/composants | Uniformiser iconographie | Pack coherent, leger, moderne |
| Leaflet | Cartographie | src/views/UserMap.vue | Besoin d affichage cartographique terrain | Open source, simple integration, flexible |
| Chart.js | Moteur de graphiques | src/components/ui/Chart.vue | Visualiser stats metier | Graphiques riches, ecosys mature |
| vue-chartjs | Wrapper Vue de Chart.js | src/components/ui/Chart.vue | Eviter du code glue manuel | Integration Vue reactive |
| vee-validate | Validation formulaires | src/components/ui/Form*.vue | Structurer validation des formulaires | Gestion ergonomique des erreurs et champs |
| yup | Schema validation | src/components/ui/Form.vue | Declarer des regles robustes | Schemas reutilisables et lisibles |
| vue-toastification | Notifications utilisateur | src/main.js, src/composables/useToast.js | Feedback UI rapide pour actions | UX claire, integration simple |
| jsPDF | Export PDF | src/views/Statistics.vue | Export de rapports | Generation PDF cote client |
| xlsx | Export Excel | src/views/Statistics.vue | Export exploitable tableur | Interoperabilite metier immediate |

## 4) Build et outillage frontend

| Technologie | Role | Ou elle sert | Pourquoi | Avantages |
|---|---|---|---|---|
| Vite (SPA maquette) | Dev server et build | Front-end/maquette/vite.config.js | Boucle dev rapide | HMR rapide, config moderne |
| @vitejs/plugin-vue | Support SFC Vue | Front-end/maquette/vite.config.js | Compiler .vue proprement | Intregration native Vue |
| vite-plugin-vue-devtools | Debug DX Vue | Front-end/maquette/vite.config.js | Faciliter debug composants | Productivite dev |
| Vite (racine Laravel) | Build assets Laravel | vite.config.js racine | Pipeline assets backend standard Laravel | Build stable et integre |
| laravel-vite-plugin | Bridge Laravel <-> Vite | package.json racine + vite.config.js | Integrer hot reload/build Laravel | Confort dev Laravel |
| Tailwind CSS 4 (racine) | Utility CSS | resources/css/app.css + plugin vite | Stylage utilitaire cote assets Laravel | Rapidite de theming |
| Bootstrap + CSS custom (maquette) | Couche visuelle principale SPA | src/main.js + src/assets/css | Time-to-market UI rapide | Composants prets + personnalisation |

## 5) Tests, lint, qualite

| Technologie | Role | Ou elle sert | Pourquoi | Avantages |
|---|---|---|---|---|
| PHPUnit | Tests backend | tests/ + phpunit.xml | Garantir non regression backend | Standard Laravel/PHP |
| Faker | Donnees fake tests | require-dev backend | Simuler donnees metier | Tests realistes |
| Mockery | Mocks tests | require-dev backend | Isoler comportements en tests | Tests unitaires plus precis |
| Laravel Pint | Lint/format PHP | CI + hook pre-commit | Uniformiser style PHP | Code plus lisible et coherent |
| PHPStan (niveau 5) | Analyse statique PHP | phpstan.neon + workflow quality | Detecter erreurs avant runtime | Qualite et robustesse accrues |
| Larastan + extensions (optionnel local) | Renfort analyse Laravel | scripts/install-linting-tools.sh | Analyse plus semantique Laravel | Meilleure detection des erreurs framework |
| Vitest | Tests unitaires frontend | scripts npm + src/tests | Tester logique/composants Vue | Rapide, proche de Vite |
| @vue/test-utils | Rendu composants en test | src/tests | Tester composants Vue realistes | API standard ecosysteme Vue |
| jsdom | DOM virtuel tests front | vite config test | Simuler navigateur en CI | Tests UI automatisables |
| ESLint | Qualite JS/Vue | eslint.config.js + CI | Eviter erreurs et anti-patterns | Code plus fiable |
| Oxlint | Lint rapide complementaire | .oxlintrc.json + CI | Feedback rapide sur patterns risqués | Performance elevee |
| Prettier | Formatage front | script npm format | Stabiliser style | Diffs propres et lisibles |

## 6) CI/CD et exploitation

| Technologie | Role | Ou elle sert | Pourquoi | Avantages |
|---|---|---|---|---|
| GitHub Actions | Automatisation CI/CD | .github/workflows | Lancer tests/build/quality automatiquement | Qualite continue, controle avant merge |
| Matrix PHP 8.2/8.3 | Compatibilite multi versions | workflow tests.yml | Verifier support versions cibles | Reduit risques de compatibilite |
| Composer audit | Securite dependances PHP | workflow quality.yml | Detecter vulnerabilites connues | Visibilite proactive securite |
| Codecov (optionnel) | Suivi couverture tests | steps CI conditionnels | Mesurer progression de couverture | Pilotage qualite base sur donnees |
| pre-commit hook | Verification locale avant commit | scripts/pre-commit | Detecter erreurs avant push | Evite allers-retours CI inutiles |

## 7) Technologies utilitaires backend

| Technologie | Role | Ou elle sert | Pourquoi | Avantages |
|---|---|---|---|---|
| Laravel Tinker | Console interactive app | require backend | Debug rapide donnees/services | Gain de temps diagnostic |
| Laravel Pail | Flux logs dev | script composer dev | Lire logs en direct pendant dev | Observabilite locale simple |
| Laravel Sail | Environnement Docker Laravel (optionnel) | require-dev backend | Option standardisee de dev containerise | Reproductibilite environnement |
| concurrently | Multi process en 1 commande | script composer dev | Lancer backend + queue + logs + vite ensemble | Productivite de demarrage |

## 8) Pourquoi cette stack est coherente pour BeneRun

- Coherence ecosysteme: Laravel + Sanctum + Reverb + Echo forment une chaine complete backend vers temps reel frontend.
- Vitesse de delivery: Vue + Vite + services API centralises accelerent l implementation des ecrans metier.
- Fonctionnalites metier riches: Leaflet, Chart.js, exports PDF/Excel couvrent cartographie, pilotage et reporting.
- Qualite integree: tests backend/frontend, lint, analyse statique et workflows CI reduisent les regressions.
- Scalabilite equipe: separation claire backend/frontend + conventions outils facilite l onboarding.

## 9) Points d attention a transmettre a une IA de redaction

- Le projet contient deux pipelines frontend:
  - assets Laravel racine (Tailwind/Vite)
  - SPA principale Front-end/maquette (Vue/Bootstrap)
- Certaines technos sont optionnelles en local (Sail, Larastan, Codecov), mais structurellement prevues.
- Les outils CI peuvent etre non bloquants sur certains jobs (ex: quality continue-on-error), a mentionner factuellement.

Cette cartographie est concue pour servir de base au manuel technique, a l onboarding et a la communication projet.
