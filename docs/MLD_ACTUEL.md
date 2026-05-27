# MLD actuel - BeneRun (mai 2026)

Ce document decrit le **modele logique de donnees actuel** a partir des migrations Laravel dans `database/migrations`.

## 1) Perimetre

- Inclus: tables metier (utilisateurs, evenements, missions, postulations, affectations, competences, badges, chat, urgences, rewards, notifications).
- Exclues du detail: tables techniques Laravel (`cache`, `jobs`, `failed_jobs`, `sessions`, etc.).
- Note: deux tables de reset mot de passe coexistent actuellement: `password_reset_tokens` et `password_resets`.

## 2) Entites metier (resume)

### users
- PK: `id_utilisateur`
- Champs cles: identite, role, contact, sante, mobilite, tshirt, anonymat, compteur missions
- Extensions live location:
  - `partage_localisation_directe_utilisateur`
  - `latitude_localisation_directe_utilisateur`
  - `longitude_localisation_directe_utilisateur`
  - `date_localisation_directe_utilisateur`

### evenements
- PK: `id_evenement`
- FK: `cree_par_utilisateur_id -> users.id_utilisateur`
- Champs cles: nom, description, dates/heures, lieu, coords, publication, annulation
- Champs localisation etendus:
  - `google_maps_url_evenement`
  - `rayon_localisation_evenement`
  - `mode_localisation_evenement` (defaut `manual`)

### missions
- PK: `id_mission`
- FK:
  - `id_evenement -> evenements.id_evenement`
  - `responsable_utilisateur_id -> users.id_utilisateur`
- Champs cles: titre, type, planning, lieu, coords, capacite, statut, visibilite, consignes
- Champ localisation etendu: `google_maps_url_mission`

### postulations
- PK: `id_postulation`
- FK:
  - `id_mission -> missions.id_mission` (nullable)
  - `id_utilisateur -> users.id_utilisateur`
  - `id_evenement -> evenements.id_evenement` (nullable, nullOnDelete)
- Contraintes:
  - unique (`id_mission`, `id_utilisateur`)
  - unique (`id_evenement`, `id_utilisateur`)

### affectations
- PK: `id_affectation`
- FK:
  - `id_mission -> missions.id_mission`
  - `id_utilisateur -> users.id_utilisateur`
- Contraintes:
  - unique (`id_mission`, `id_utilisateur`)
- Champ recent ajoute: `heure_rendez_vous_affectation` (nullable)

### certificats
- PK: `id_certificat`
- FK: `id_utilisateur -> users.id_utilisateur`

### badges
- PK: `id_badge`

### user_badges (pivot)
- PK composee: (`id_utilisateur`, `id_badge`)
- FK:
  - `id_utilisateur -> users.id_utilisateur`
  - `id_badge -> badges.id_badge`

### competences
- PK: `id_competence`
- Contrainte: `nom_competence` unique
- Extension recente: `types_mission_suggeres` (JSON nullable)

### user_competences (pivot)
- PK composee: (`id_utilisateur`, `id_competence`)
- FK vers `users`, `competences`
- Champ: `niveau` (nullable)

### mission_competences (pivot)
- PK composee: (`id_mission`, `id_competence`)
- FK vers `missions`, `competences`

### mission_contacts
- PK: `id_contact_mission`
- FK: `id_mission -> missions.id_mission`

### mission_medias
- PK: `id_media_mission`
- FK:
  - `id_mission -> missions.id_mission`
  - `telecharge_par_utilisateur_id -> users.id_utilisateur` (nullable, nullOnDelete)

### favorites (pivot)
- PK composee: (`id_utilisateur`, `id_mission`)
- FK vers `users`, `missions`

### mission_positions
- PK: `id_position`
- FK vers `missions`, `users`
- Contrainte: unique (`id_mission`, `id_utilisateur`)

### mission_emergency_messages
- PK: `id_mission_emergency_message`
- FK:
  - `id_mission -> missions.id_mission`
  - `id_evenement -> evenements.id_evenement`
  - `id_emetteur_utilisateur -> users.id_utilisateur`
  - `pris_en_charge_par_utilisateur_id -> users.id_utilisateur` (nullable, nullOnDelete)

### mission_emergency_message_views
- PK: `id_mission_emergency_message_view`
- FK:
  - `id_mission_emergency_message -> mission_emergency_messages.id_mission_emergency_message`
  - `id_utilisateur -> users.id_utilisateur`
- Contrainte: unique (`id_mission_emergency_message`, `id_utilisateur`)

### chat_conversations
- PK: `id_chat_conversation`
- FK:
  - `id_mission -> missions.id_mission` (nullable, nullOnDelete)
  - `created_by_utilisateur_id -> users.id_utilisateur` (nullable, nullOnDelete)

### chat_conversation_participants
- PK: `id_chat_conversation_participant`
- FK vers `chat_conversations`, `users`
- Contrainte: unique (`id_chat_conversation`, `id_utilisateur`)

### chat_messages
- PK: `id_chat_message`
- FK:
  - `id_chat_conversation -> chat_conversations.id_chat_conversation`
  - `id_sender_utilisateur -> users.id_utilisateur`

### chat_message_reads
- PK: `id_chat_message_read`
- FK vers `chat_messages`, `users`
- Contrainte: unique (`id_chat_message`, `id_utilisateur`)

### mission_reward_competences (pivot enrichi)
- PK composee: (`id_mission`, `id_competence`)
- FK vers `missions`, `competences`
- Champ metier: `points_gagnes`

### user_competence_points (agregat)
- PK composee: (`id_utilisateur`, `id_competence`)
- FK vers `users`, `competences`
- Champ metier: `points_total`

### badge_competence_rules
- PK: `id_badge_competence_rule`
- FK vers `badges`, `competences`
- Contrainte: unique (`id_badge`, `id_competence`)
- Champ metier: `points_requis`

### mission_user_rewards
- PK: `id_mission_user_reward`
- FK vers `missions`, `users`
- Contrainte: unique (`id_mission`, `id_utilisateur`)
- Champ metier: `details_recompense` (JSON nullable)

### notification_reads
- PK: `id_notification_read`
- FK: `id_utilisateur -> users.id_utilisateur`
- Contrainte: unique (`id_utilisateur`, `notification_key`)

## 3) Enumerations metier

### users.role_utilisateur
- bénévole
- responsable
- admin
- superadmin

### users.taille_tshirt_utilisateur
- XS, S, M, L, XL

### missions.type_mission
- secours
- logistique
- accueil
- technique
- animation
- autre

### missions.statut_mission
- À venir
- En cours
- Terminée
- Annulée

### missions.visibilite_mission
- publique
- privée
- limitée

### postulations.statut_postulation
- en_attente
- accepte
- refuse
- annule

### affectations.statut_affectation
- assigne
- confirme
- present
- absent
- annule

### certificats.type_certificat
- platform
- external

### certificats.statut_certificat
- en attente
- approuvé
- rejeté

## 4) Relations principales (cardinalites)

- 1 user cree N evenements
- 1 evenement contient N missions
- 1 user est responsable de N missions
- N users <-> N missions via postulations
- N users <-> N missions via affectations
- N users <-> N competences via user_competences
- N missions <-> N competences via mission_competences
- N users <-> N missions via favorites
- N users <-> N badges via user_badges
- 1 mission a N contacts
- 1 mission a N medias
- 1 mission a N positions utilisateur (1 max par user)
- 1 mission a N messages d'urgence
- 1 message d'urgence a N vues
- 1 conversation chat a N participants
- 1 conversation chat a N messages
- 1 message chat a N accusés de lecture
- N missions <-> N competences via mission_reward_competences
- N users <-> N competences via user_competence_points
- N badges <-> N competences via badge_competence_rules
- N users <-> N missions via mission_user_rewards

## 5) Principales differences par rapport a votre ancien MLD

- Ajout de la messagerie complete (`chat_conversations`, participants, messages, reads).
- Ajout du workflow urgences mission (`mission_emergency_messages`, vues, prise en charge).
- Ajout de la geolocalisation etendue:
  - URL Google Maps + rayon + mode sur evenements
  - URL Google Maps sur missions
  - localisation directe live sur users
  - positions mission en temps reel (`mission_positions`)
- Ajout du systeme de rewards competences/badges:
  - `mission_reward_competences`
  - `user_competence_points`
  - `badge_competence_rules`
  - `mission_user_rewards`
- Extension postulation: rattachement optionnel evenement (`id_evenement`) + contrainte d'unicite par evenement/utilisateur.
- Ajout du suivi de lecture des notifications (`notification_reads`).
- Ajout de `heure_rendez_vous_affectation` dans `affectations`.

## 6) Source de verite

Pour regeneration fiable: utiliser les migrations de `database/migrations` comme source prioritaire du schema.
