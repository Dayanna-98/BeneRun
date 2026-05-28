# MLD textuel - BeneRun (regenere automatiquement)

Date: 2026-05-27
Source: database/migrations (Schema::create + Schema::table + contraintes foreign).

## 1) Tables de la BDD et tous leurs champs

### affectations
- Champs: created_at, date_affectation, date_confirmation, date_presence, est_responsable, heure_rendez_vous_affectation, id_affectation, id_mission, id_utilisateur, remarque, statut_affectation, updated_at

### badge_competence_rules
- Champs: created_at, id_badge, id_badge_competence_rule, id_competence, points_requis, updated_at

### badges
- Champs: created_at, description_badge, icone_badge, id_badge, regle_auto, score_badge, titre_badge, updated_at

### cache
- Champs: expiration, key, value

### cache_locks
- Champs: expiration, key, owner

### certificats
- Champs: chemin_fichier_certificat, created_at, date_emission_certificat, date_expiration_certificat, emetteur_certificat, id_certificat, id_utilisateur, statut_certificat, titre_certificat, type_certificat, updated_at

### chat_conversation_participants
- Champs: created_at, id_chat_conversation, id_chat_conversation_participant, id_utilisateur, joined_at, last_read_at, updated_at

### chat_conversations
- Champs: created_at, created_by_utilisateur_id, id_chat_conversation, id_mission, last_message_at, titre_conversation, type_conversation, updated_at

### chat_message_reads
- Champs: created_at, id_chat_message, id_chat_message_read, id_utilisateur, read_at, updated_at

### chat_messages
- Champs: contenu_message, created_at, id_chat_conversation, id_chat_message, id_sender_utilisateur, type_message, updated_at

### competences
- Champs: created_at, id_competence, nom_competence, types_mission_suggeres, updated_at

### evenements
- Champs: created_at, cree_par_utilisateur_id, date_annulation_evenement, date_debut_evenement, date_fin_evenement, description_evenement, est_annule_evenement, est_publie_evenement, google_maps_url_evenement, heure_debut_evenement, heure_fin_evenement, id_evenement, image_evenement, latitude_evenement, lieu_evenement, longitude_evenement, mode_localisation_evenement, nom_evenement, nombre_benevoles_requis, organisateur_evenement, raison_annulation_evenement, rayon_localisation_evenement, updated_at

### failed_jobs
- Champs: connection, exception, failed_at, payload, queue, uuid

### favorites
- Champs: created_at, id_mission, id_utilisateur, updated_at

### job_batches
- Champs: cancelled_at, created_at, failed_job_ids, failed_jobs, finished_at, id, name, options, pending_jobs, total_jobs

### jobs
- Champs: attempts, available_at, created_at, payload, queue, reserved_at

### mission_competences
- Champs: created_at, id_competence, id_mission, updated_at

### mission_contacts
- Champs: created_at, email_contact, est_contact_jour_j, est_contact_principal, id_contact_mission, id_mission, nom_contact, telephone_contact, updated_at

### mission_emergency_message_views
- Champs: consulte_le, created_at, id_mission_emergency_message, id_mission_emergency_message_view, id_utilisateur, updated_at

### mission_emergency_messages
- Champs: categorie_urgence, created_at, id_emetteur_utilisateur, id_evenement, id_mission, id_mission_emergency_message, message_urgence, pris_en_charge_le, pris_en_charge_par_utilisateur_id, updated_at

### mission_medias
- Champs: chemin_fichier, created_at, id_media_mission, id_mission, taille_fichier, telecharge_par_utilisateur_id, type_mime, updated_at

### mission_positions
- Champs: created_at, id_mission, id_position, id_utilisateur, latitude, longitude, updated_at

### mission_reward_competences
- Champs: created_at, id_competence, id_mission, points_gagnes, updated_at

### mission_user_rewards
- Champs: created_at, details_recompense, id_mission, id_mission_user_reward, id_utilisateur, rewarded_at, updated_at

### missions
- Champs: consignes_securite, created_at, date_mission, description_mission, google_maps_url_mission, heure_debut_mission, heure_fin_mission, id_evenement, id_mission, image_mission, inscription_requise, latitude_mission, lieu_mission, longitude_mission, nombre_benevoles_backup, nombre_benevoles_max, publie_le_mission, responsable_utilisateur_id, statut_mission, titre_mission, type_mission, updated_at, visibilite_mission

### notification_reads
- Champs: created_at, id_notification_read, id_utilisateur, notification_key, read_at, updated_at

### password_reset_tokens
- Champs: created_at, email, token

### password_resets
- Champs: created_at, email, token

### personal_access_tokens
- Champs: abilities, created_at, expires_at, last_used_at, name, token, tokenable_id, tokenable_type, updated_at

### postulations
- Champs: created_at, date_annulation, date_decision, date_postulation, id_evenement, id_mission, id_postulation, id_utilisateur, remarque, statut_postulation, updated_at

### sessions
- Champs: id, ip_address, last_activity, payload, user_agent, user_id

### user_badges
- Champs: attribue_le, created_at, id_badge, id_utilisateur, updated_at

### user_competence_points
- Champs: created_at, id_competence, id_utilisateur, points_total, updated_at

### user_competences
- Champs: created_at, id_competence, id_utilisateur, niveau, updated_at

### users
- Champs: adresse_utilisateur, allergies_utilisateur, created_at, date_localisation_directe_utilisateur, date_naissance_utilisateur, deleted_at, email, email_verified_at, est_anonyme_utilisateur, est_motorise_utilisateur, id_utilisateur, latitude_localisation_directe_utilisateur, longitude_localisation_directe_utilisateur, nom_utilisateur, nombre_missions_utilisateur, partage_localisation_directe_utilisateur, password, permissions_utilisateur, possede_permis_utilisateur, possede_vehicule_utilisateur, prenom_utilisateur, problemes_sante_utilisateur, remember_token, role_utilisateur, taille_tshirt_utilisateur, telephone_utilisateur, updated_at

## 2) Relations / connexions entre tables (FK explicites)

Cardinalite de lecture: N -> 1 depuis la table source vers la table cible.

- affectations.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- affectations.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- badge_competence_rules.id_badge -> badges.id_badge (N -> 1, onDelete: cascadeOnDelete)
- badge_competence_rules.id_competence -> competences.id_competence (N -> 1, onDelete: cascadeOnDelete)
- certificats.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- chat_conversation_participants.id_chat_conversation -> chat_conversations.id_chat_conversation (N -> 1, onDelete: cascadeOnDelete)
- chat_conversation_participants.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- chat_conversations.created_by_utilisateur_id -> users.id_utilisateur (N -> 1, onDelete: nullOnDelete)
- chat_conversations.id_mission -> missions.id_mission (N -> 1, onDelete: nullOnDelete)
- chat_message_reads.id_chat_message -> chat_messages.id_chat_message (N -> 1, onDelete: cascadeOnDelete)
- chat_message_reads.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- chat_messages.id_chat_conversation -> chat_conversations.id_chat_conversation (N -> 1, onDelete: cascadeOnDelete)
- chat_messages.id_sender_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- evenements.cree_par_utilisateur_id -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- favorites.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- favorites.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- mission_competences.id_competence -> competences.id_competence (N -> 1, onDelete: cascadeOnDelete)
- mission_competences.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- mission_contacts.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- mission_emergency_message_views.id_mission_emergency_message -> mission_emergency_messages.id_mission_emergency_message (N -> 1, onDelete: cascadeOnDelete)
- mission_emergency_message_views.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- mission_emergency_messages.id_emetteur_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- mission_emergency_messages.id_evenement -> evenements.id_evenement (N -> 1, onDelete: cascadeOnDelete)
- mission_emergency_messages.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- mission_emergency_messages.pris_en_charge_par_utilisateur_id -> users.id_utilisateur (N -> 1, onDelete: nullOnDelete)
- mission_medias.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- mission_medias.telecharge_par_utilisateur_id -> users.id_utilisateur (N -> 1, onDelete: nullOnDelete)
- mission_reward_competences.id_competence -> competences.id_competence (N -> 1, onDelete: cascadeOnDelete)
- mission_reward_competences.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- mission_user_rewards.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- mission_user_rewards.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- missions.id_evenement -> evenements.id_evenement (N -> 1, onDelete: cascadeOnDelete)
- missions.responsable_utilisateur_id -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- notification_reads.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- postulations.id_evenement -> evenements.id_evenement (N -> 1, onDelete: nullOnDelete)
- postulations.id_mission -> missions.id_mission (N -> 1, onDelete: cascadeOnDelete)
- postulations.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- user_badges.id_badge -> badges.id_badge (N -> 1, onDelete: cascadeOnDelete)
- user_badges.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- user_competence_points.id_competence -> competences.id_competence (N -> 1, onDelete: cascadeOnDelete)
- user_competence_points.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)
- user_competences.id_competence -> competences.id_competence (N -> 1, onDelete: cascadeOnDelete)
- user_competences.id_utilisateur -> users.id_utilisateur (N -> 1, onDelete: cascadeOnDelete)

## 3) Relations logiques sans FK SQL explicite

- sessions.user_id correspond logiquement a users.id_utilisateur (pas de FK declaree).
- password_reset_tokens.email et password_resets.email correspondent logiquement a users.email (pas de FK declaree).
- personal_access_tokens.tokenable_type + tokenable_id est une relation polymorphique (pas de FK SQL stricte).

## 4) Resume de couverture

- Nombre total de tables detectees: 35
- Nombre total de relations FK detectees: 43

