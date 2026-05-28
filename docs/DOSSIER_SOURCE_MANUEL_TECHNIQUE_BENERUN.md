# Dossier source pour redaction du manuel technique BeneRun

Date de generation: 2026-05-28

Objectif: fournir a une IA de redaction l ensemble des informations verifiables necessaires pour produire le manuel technique final, sans acces direct au code.

Regle de fiabilite appliquee: ce dossier ne contient que des informations observees dans le depot (fichiers de configuration, routes, schema, services, README, tests). Les points incertains sont explicitement signales.

## 1) Resume du projet

- Type de projet: application web de gestion de benevoles pour evenements et missions.
- Stack backend: Laravel 12, PHP 8.2+, Sanctum (auth API), Reverb (WebSocket), Scramble (doc OpenAPI).
- Stack frontend principale (application utilisateur): Vue 3 + Vite, dans Front-end/maquette.
- Frontend Laravel classique present (resources/js + vite) mais l application principale est servie en SPA sous /app/.
- Base de donnees: MySQL en local (tests en SQLite in-memory).

## 2) Arborescence fonctionnelle a connaitre

### Backend Laravel
- app/Http/Controllers: controleurs API metier (users, missions, evenements, postulations, affectations, chat, urgences, stats, maps, etc.).
- app/Models: modeles Eloquent metier.
- app/Services: service metier MissionRewardService.
- routes/api.php: routes REST/API.
- routes/web.php: redirection / -> /app/ + serving SPA.
- routes/channels.php: autorisations de canaux broadcast.
- database/migrations: source de verite du schema SQL.
- database/seeders: donnees de demonstration.
- config: auth, mail, cors, broadcasting, etc.

### Frontend Vue (application principale)
- Front-end/maquette/src/router/index.js: routes client et guards role/auth.
- Front-end/maquette/src/views: pages metier.
- Front-end/maquette/src/services: appels API centralises.
- Front-end/maquette/vite.config.js: base '/app/', port 5173.

## 3) Prerequis techniques verifies

- PHP: ^8.2 (composer.json)
- Node.js: ^20.19.0 ou >=22.12.0 (Front-end/maquette/package.json)
- Composer
- npm
- MySQL (README recommande XAMPP/WAMP sous Windows)
- Git

## 4) Installation locale et demarrage (source README)

Ordre recommande:

1. Demarrer MySQL
2. Backend (dossier BeneRun):
   - composer install
   - npm install
   - copier .env.example vers .env
   - php artisan key:generate
   - php artisan migrate --force
   - php artisan optimize:clear
3. Frontend principal (dossier Front-end/maquette):
   - copier .env.example vers .env
   - npm install
4. Lancer 3 terminaux:
   - php artisan serve
   - php artisan reverb:start
   - npm run dev (dans Front-end/maquette)

URLs locales usuelles:
- Frontend: http://localhost:5173
- API backend: http://127.0.0.1:8000
- Doc API: http://127.0.0.1:8000/docs/api#/

## 5) Configuration environnement

## 5.1 Variables backend importantes (.env.example)

Application:
- APP_NAME
- APP_ENV
- APP_KEY
- APP_DEBUG
- APP_URL
- APP_LOCALE
- APP_FALLBACK_LOCALE

Base de donnees:
- DB_CONNECTION
- DB_HOST
- DB_PORT
- DB_DATABASE
- DB_USERNAME
- DB_PASSWORD

Session/cache/queue:
- SESSION_DRIVER
- SESSION_LIFETIME
- CACHE_STORE
- QUEUE_CONNECTION

Temps reel (Reverb):
- BROADCAST_CONNECTION
- REVERB_APP_ID
- REVERB_APP_KEY
- REVERB_APP_SECRET
- REVERB_HOST
- REVERB_PORT
- REVERB_SCHEME
- REVERB_SERVER_HOST
- REVERB_SERVER_PORT

Mail:
- MAIL_MAILER
- MAIL_SCHEME (avec fallback legacy MAIL_ENCRYPTION dans config/mail.php)
- MAIL_HOST
- MAIL_PORT
- MAIL_USERNAME
- MAIL_PASSWORD
- MAIL_FROM_ADDRESS
- MAIL_FROM_NAME

Liens frontend dans emails:
- FRONTEND_URL
- FRONTEND_RESET_PASSWORD_URL_TEMPLATE
- FRONTEND_EMAIL_VERIFICATION_URL_TEMPLATE

## 5.2 Variables frontend importantes (Front-end/maquette/.env.example)
- VITE_API_BASE_URL
- VITE_REVERB_APP_KEY
- VITE_REVERB_HOST
- VITE_REVERB_PORT
- VITE_REVERB_SCHEME

## 5.3 CORS et integration navigateur

config/cors.php autorise notamment:
- http://localhost:5173
- http://127.0.0.1:5173
- http://localhost:5174
- http://127.0.0.1:5174

Paths CORS principaux:
- api/*
- broadcasting/auth
- sanctum/csrf-cookie

## 6) Vue d ensemble architecture

- Routing web Laravel:
  - / redirige (302) vers /app/
  - /app/{any?} sert le build SPA (public/app/index.html)
- API REST dans routes/api.php
- Auth API principalement via Sanctum (Bearer token cote frontend via localStorage)
- Temps reel:
  - backend broadcasting Reverb
  - canaux prives authorises dans routes/channels.php
  - frontend Echo/Pusher dans src/services/realtime.js
- Fallback messagerie:
  - polling/frontend pour synchronisation (a confirmer dans Messaging.vue)

## 7) Organisation metier (backend)

## 7.1 Controleurs presents
- AdminController
- AffectationController
- BadgeController
- BenevoleController
- CertificatController
- ChatConversationController
- ChatMessageController
- CompetenceController
- CourseController
- DocumentController
- EvenementController
- FavoriteController
- LocationController
- MapsController
- MissionController
- MissionEmergencyMessageController
- MissionPositionController
- NotificationController
- PasswordResetController
- PostulationController
- StatsController
- TelephoneController
- UserController

## 7.2 Modeles presents
- Affectation
- Badge
- BadgeCompetenceRule
- Certificat
- ChatConversation
- ChatConversationParticipant
- ChatMessage
- ChatMessageRead
- Competence
- Evenement
- Mission
- MissionContact
- MissionEmergencyMessage
- MissionEmergencyMessageView
- MissionMedia
- MissionPosition
- MissionRewardCompetence
- MissionUserReward
- NotificationRead
- Postulation
- User
- UserCompetencePoint

## 7.3 Service metier present
- MissionRewardService
  - Attribution des points de competences aux participants d une mission terminee
  - Attribution automatique de badges selon seuils badge_competence_rules
  - Idempotence par mission_user_rewards (evite double attribution)

## 8) Frontend principal (Vue)

## 8.1 Routes frontend importantes

Routes publiques:
- /login
- /register
- /reset-password
- /email-verification
- /cgu

Routes protegees (extraits):
- /
- /missions
- /events
- /events-list
- /event/:id
- /my-missions
- /messagerie
- /profile
- /mission/:id
- /favorites
- /manage-missions
- /manage-events
- /manage-users
- /statistics
- /manage-competences
- /manage-badges
- /manage-certificates
- /map

Guard frontend:
- requiresAuth sur la majorite des pages
- requiredRole sur certaines pages (ex: statistics admin/superadmin, create user superadmin)

Historique router:
- createWebHistory('/app/')

## 8.2 Vues frontend disponibles
- Cgu
- CreateEvent
- CreateMission
- CreateUser
- Dashboard
- EditEvent
- EditMission
- EditProfile
- EditUser
- EmailVerification
- EventDetails
- Events
- EventsList
- Favorites
- Login
- ManageBadges
- ManageCertificates
- ManageCompetences
- ManageEvents
- ManageMissions
- ManageUsers
- Messaging
- MissionDetails
- Missions
- MyManagedMissions
- MyMissions
- Profile
- Register
- ResetPassword
- Statistics
- UserMap
- Welcome

## 8.3 Services frontend (API consommee)
- api.js: axios instance + token Bearer + redirection login sur 401
- userService.js: login/logout/me/users + badges/competences utilisateur
- missionService.js: CRUD missions + maps resolve + payload competences/rewards
- eventService.js: CRUD evenements + payload multipart
- competenceService.js: CRUD competences + assignations user
- badgeService.js: CRUD badges + assignations user
- certificatService.js: CRUD certificats + download
- chatApiService.js: conversations/messages/read receipts
- realtime.js: Echo/Reverb + auth broadcasting
- emergencyService.js: urgences mission (creation, consultation, prise en charge)
- notificationService.js: feed et marquage lu
- chatService.js: store localStorage legacy (encore present dans code)

## 9) Modele de donnees (source de verite)

Source prioritaire:
- docs/MLD_TEXTUEL_BENERUN.md
- docs/MLD_ACTUEL.md
- database/migrations

Elements verifies:
- Nombre de tables detectees dans MLD textuel: 35
- Nombre de FK detectees: 43
- Entites metier majeures: users, evenements, missions, postulations, affectations, competences, badges, certificats, chat_*, mission_emergency_*, mission_positions, favorites, notification_reads, reward tables.

Enumerations metier explicites (MLD actuel):
- users.role_utilisateur: benevole, responsable, admin, superadmin
- missions.type_mission: secours, logistique, accueil, technique, animation, autre
- missions.statut_mission: A venir, En cours, Terminee, Annulee
- missions.visibilite_mission: publique, privee, limitee
- postulations.statut_postulation: en_attente, accepte, refuse, annule
- affectations.statut_affectation: assigne, confirme, present, absent, annule
- certificats.type_certificat: platform, external
- certificats.statut_certificat: en attente, approuve, rejete

## 10) Migrations detectees (chronologie utile)

- 0001_01_01_000000_create_users_table
- 0001_01_01_000001_create_cache_table
- 0001_01_01_000002_create_jobs_table
- 2026_01_29_171900_create_evenements_table
- 2026_01_30_094633_create_badges_table
- 2026_01_30_102735_create_certificats_table
- 2026_01_30_103422_create_missions_table
- 2026_01_30_113811_create_postulations_table
- 2026_01_30_124606_create_affectations_table
- 2026_04_03_200330_create_mission_contacts_table
- 2026_04_03_201047_create_mission_media_table
- 2026_04_03_201440_create_competences_table
- 2026_04_03_202722_create_mission_competence_table
- 2026_04_03_202941_create_user_competences_table
- 2026_04_03_213846_create_user_badges_table
- 2026_04_03_220126_create_favorites_table
- 2026_04_16_212915_create_personal_access_tokens_table
- 2026_04_27_120000_add_google_maps_fields_to_evenements_and_missions
- 2026_04_27_140000_add_live_location_fields_to_users_table
- 2026_04_28_120000_add_event_id_to_postulations_table
- 2026_04_29_090000_create_mission_emergency_messages_tables
- 2026_04_30_144542_create_mission_positions_table
- 2026_05_06_000000_create_password_resets_table
- 2026_05_11_120000_create_chat_tables
- 2026_05_11_200000_add_mission_types_to_competences_table
- 2026_05_12_100000_create_mission_reward_competences_table
- 2026_05_12_100100_create_user_competence_points_table
- 2026_05_12_100200_create_badge_competence_rules_table
- 2026_05_12_100300_create_mission_user_rewards_table
- 2026_05_15_120000_add_location_mode_to_evenements_table
- 2026_05_24_120000_create_notification_reads_table
- 2026_05_27_120000_add_heure_rendez_vous_to_affectations_table

## 11) Seeders et donnees de demonstration

Seeders detectes:
- DatabaseSeeder.php
- SeedLiveTest.php
- 2026 TGG  ORGANISATION.csv (fichier present)

Comportements importants DatabaseSeeder:
- Truncate de nombreuses tables metier (pas users)
- Si users vide: seedUsers
- Sinon: map des roles existants pour reutiliser des IDs
- Si users toujours vide: RuntimeException('Aucun utilisateur trouve.')
- Cree jeux de donnees evenements/missions/postulations/affectations/favoris/certificats/urgences

## 12) Securite et controle d acces

Elements verifies:
- Middleware global SecurityHeaders applique dans bootstrap/app.php
- Limiteurs de debit nommes dans AppServiceProvider:
  - auth-login
  - password-reset
  - email-verification
  - maps-resolve
  - emergency-actions
  - notifications-read
- Auth API: Sanctum (auth:sanctum sur de nombreuses routes)
- Broadcasting auth protege (bootstrap/app.php + channels)

Headers de securite ajoutes (SecurityHeaders):
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy: camera=(), microphone=(), geolocation=(self)
- Content-Security-Policy adaptee docs/API vs application
- HSTS si HTTPS

## 13) API et routes

Une reference exhaustive prete a l emploi est fournie dans:
- docs/REFERENCE_ROUTES_API_BENERUN.md

Ce fichier inclut:
- routes web
- routes channels
- routes API explicites
- expansion des apiResource (users, missions, evenements, etc.)
- infos auth/throttle

## 14) Tests et couverture fonctionnelle disponible

Feature tests detectes:
- AffectationCRUDTest
- BadgeCRUDTest
- CertificatCRUDTest
- ChatMessagingTest
- ChatMissionGroupTest
- CompetenceCRUDTest
- DatabaseSeederTest
- EvenementCRUDTest
- FavoritesTest
- LocationApiTest
- MapsResolveApiTest
- MissionCRUDTest
- MissionEmergencyMessageTest
- MissionLocationPerimeterTest
- MissionPositionApiTest
- NotificationFeedTest
- NotificationsTest
- PasswordResetFlowTest
- PostulationUpdateDestroyTest
- PostulationWorkflowTest
- StatsTest
- UserAuthFlowTest
- UserManagementSecurityTest
- UserRoleUpdateTest

Unit tests detectes:
- GoogleMapsUrlTest
- MissionRewardServiceTest

Config test backend (phpunit.xml):
- DB sqlite in-memory
- BROADCAST_CONNECTION=null
- MAIL_MAILER=array

## 15) Points de vigilance / zones a signaler dans le futur manuel

- Deux frontends coexistent techniquement:
  - Laravel resources/js (vite backend)
  - Vue principale Front-end/maquette (application utilisateur)
  Le manuel doit clairement dire lequel sert a quoi.

- Deux tables de reset password coexistent (MLD):
  - password_reset_tokens
  - password_resets
  A documenter comme etat actuel du schema.

- Certaines routes API ne sont pas protegees par auth:sanctum (selon routes/api.php), meme pour des ressources sensibles.
  A signaler factuellement sans conclure a un bug si non confirme par besoin metier.

- chatService.js (localStorage) est encore present en plus de chatApiService.js.
  Le manuel doit decrire l etat courant sans supposer une suppression complete de l ancien flux.

## 16) Materiel brut a fournir a l IA de redaction (checklist)

- README backend
- README frontend
- docs/MLD_TEXTUEL_BENERUN.md
- docs/MLD_ACTUEL.md
- routes/api.php
- routes/web.php
- routes/channels.php
- .env.example backend
- Front-end/maquette/.env.example
- composer.json
- package.json (backend)
- Front-end/maquette/package.json
- vite.config.js (backend)
- Front-end/maquette/vite.config.js
- liste des controllers/modeles/services/views
- migrations + seeders
- reference des routes API (fichier dedie)

Ce dossier couvre la matiere necessaire pour rediger un manuel technique complet conforme au brief BRIEF_MANUEL_TECHNIQUE_AI.md.
