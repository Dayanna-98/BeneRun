# Brief de rédaction - Manuel technique BeneRun

Ce fichier sert de brief prêt à l'emploi pour une IA de rédaction. Son objectif est de produire un **manuel de guide technique complet, clair, accessible et fiable** pour l'application BeneRun.

## Rôle demandé à l'IA

Tu es un **rédacteur technique senior** spécialisé dans les applications web Laravel + Vue.

Ta mission est de rédiger un **manuel technique final** pour BeneRun, destiné à un public mixte:

- nouveaux membres de l'équipe projet
- développeurs backend et frontend
- responsables techniques ou fonctionnels
- personnes non spécialistes qui doivent comprendre l'application sans jargon inutile

## Objectif du document final

Le document final doit permettre à un lecteur de:

- comprendre le but de l'application et son périmètre
- identifier les grandes briques techniques
- installer et lancer le projet en local
- comprendre l'architecture générale
- connaître les principales règles métier
- savoir où intervenir pour modifier une fonctionnalité
- diagnostiquer les problèmes courants

Le document doit être écrit en **français**, dans un style **professionnel, précis et pédagogique**.

## Règles de qualité

Le document final doit respecter les règles suivantes:

- ne pas inventer d'informations non vérifiées
- s'appuyer uniquement sur les fichiers fournis et sur le code du projet
- expliquer les termes techniques lorsqu'ils apparaissent
- privilégier des phrases courtes et des titres explicites
- éviter le langage marketing ou vague
- garder un ton neutre et utile
- faire ressortir les commandes, chemins et variables d'environnement de manière lisible
- distinguer clairement ce qui est obligatoire, optionnel, et contextuel

Si une information n'est pas certaine, il faut le signaler au lieu de l'affirmer.

## Sources à consulter en priorité

L'IA doit consulter et recouper les informations suivantes:

- `README.md`
- `docs/MLD_TEXTUEL_BENERUN.md`
- `docs/MLD_ACTUEL.md`
- `composer.json`
- `package.json`
- `vite.config.js`
- `routes/web.php`
- `routes/api.php`
- `routes/channels.php`
- `app/Http/Controllers/`
- `app/Models/`
- `app/Services/`
- `resources/views/`
- `resources/js/`
- `config/`
- `database/migrations/`
- `database/seeders/`

## Structure attendue du manuel final

Le manuel final doit suivre une structure cohérente et progressive. Propose au minimum les sections ci-dessous.

### 1. Présentation générale

Expliquer:

- ce qu'est BeneRun
- à qui l'application s'adresse
- les grands objectifs métier
- les rôles principaux utilisateurs

### 2. Vue d'ensemble de l'architecture

Décrire:

- le découpage backend / frontend
- le rôle de Laravel
- le rôle de Vue.js / Vite
- la couche base de données
- le temps réel et la messagerie si applicable
- la logique API

### 3. Prérequis techniques

Lister:

- version PHP attendue
- version Node.js attendue
- Composer
- MySQL ou équivalent
- Git
- environnement Windows si des particularités existent

Préciser les points de vigilance d'installation.

### 4. Installation locale pas à pas

Expliquer de manière simple et chronologique:

- clonage du dépôt
- installation des dépendances backend
- installation des dépendances frontend
- création des fichiers `.env`
- configuration de la base de données
- génération de la clé Laravel
- migrations
- seeders si nécessaires

### 5. Lancement en développement

Documenter:

- les commandes à lancer côté backend
- les commandes à lancer côté frontend
- les services annexes à démarrer si nécessaire
- l'ordre conseillé pour démarrer l'application
- les URL locales à ouvrir

### 6. Configuration

Présenter clairement:

- les variables d'environnement backend importantes
- les variables d'environnement frontend importantes
- les paramètres liés à la base de données
- les paramètres mail si pertinents
- les paramètres de broadcast / temps réel si pertinents

### 7. Modèle de données

Décrire de façon structurée:

- les tables principales
- les relations entre tables
- les contraintes métier visibles dans la base
- les tables techniques à ne pas confondre avec les tables métier

Utiliser le contenu de `docs/MLD_TEXTUEL_BENERUN.md` comme base de vérité pour cette section.

### 8. Fonctionnalités principales

Présenter les fonctionnalités métier de l'application, par exemple:

- authentification et gestion utilisateur
- événements
- missions
- affectations et postulations
- compétences et badges
- messagerie
- notifications
- localisation / cartes / positions si applicable
- fonctionnalités d'administration

Pour chaque fonctionnalité, expliquer:

- son but
- les acteurs concernés
- les données manipulées
- les points d'entrée techniques

### 9. API et routes

Expliquer les routes importantes:

- routes web
- routes API
- routes de broadcasting / channels

Pour les endpoints importants, documenter:

- méthode HTTP
- chemin
- rôle
- authentification requise ou non
- erreurs courantes

### 10. Organisation du code

Décrire comment le projet est organisé:

- où trouver les contrôleurs
- où trouver les modèles
- où trouver les vues frontend
- où trouver les services métier
- où trouver les migrations et seeders
- où trouver la configuration

### 11. Flux métiers importants

Rédiger des explications de séquences métier, par exemple:

- création d'une mission
- inscription d'un bénévole
- affectation à une mission
- échange de messages
- notification d'événements ou de missions
- partage de localisation si utilisé

Chaque flux doit être expliqué simplement, étape par étape.

### 12. Sécurité et bonnes pratiques

Inclure:

- authentification
- autorisations
- protection des routes
- validation des données
- gestion des erreurs
- bonnes pratiques d'exploitation

### 13. Déploiement et exploitation

Si les informations sont disponibles, décrire:

- le déploiement
- les commandes utiles en production
- le nettoyage du cache
- la mise à jour de la base
- les vérifications après déploiement

### 14. Dépannage

Ajouter une section très utile avec:

- symptômes fréquents
- causes probables
- vérifications à faire
- actions correctives

Exemples de thèmes à couvrir:

- erreur 500
- base de données inaccessible
- frontend qui ne démarre pas
- problème de compilation Vite
- problème de variables `.env`
- problème de temps réel ou de messagerie

### 15. Glossaire

Définir les termes techniques utilisés dans le manuel, par exemple:

- backend
- frontend
- API
- migration
- seeder
- broadcast
- websocket
- token
- auth

### 16. Annexes

Ajouter si utile:

- arborescence simplifiée du projet
- commandes courantes
- variables d'environnement de référence
- tableau des modules principaux

## Exigences de style

Le document final doit être:

- structuré avec des titres H1, H2, H3 cohérents
- lisible pour un débutant complet
- suffisamment détaillé pour un usage technique réel
- sans répétitions inutiles
- avec des listes à puces quand cela améliore la lecture
- avec des blocs de code pour les commandes et exemples de configuration

Privilégier:

- des tableaux pour résumer des paramètres ou des routes
- des listes pour les étapes
- des paragraphes courts pour les explications

## Format de sortie attendu

Produis un document final en Markdown, prêt à être déposé dans `docs/` ou dans un livrable séparé.

Le rendu final doit être autonome, c'est-à-dire compréhensible sans autre contexte.

## Vérification finale obligatoire

Avant de terminer, vérifie que le document final:

- couvre bien l'installation, l'exécution, l'architecture, les données et le dépannage
- n'oublie pas les variables d'environnement importantes
- reste compréhensible pour un lecteur non expert
- ne contient pas d'affirmations non sourcées
- est cohérent avec le code et la documentation du projet

## Consigne finale à l'IA

Rédige maintenant le manuel technique complet de BeneRun en suivant strictement ce brief, avec un niveau de qualité adapté à une documentation projet sérieuse.