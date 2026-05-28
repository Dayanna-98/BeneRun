# Reference routes BeneRun (web + api + channels)

Date de generation: 2026-05-28

Source unique: routes/web.php, routes/api.php, routes/channels.php.

Convention de lecture:
- Auth: indique si auth:sanctum est explicitement applique dans routes.
- Throttle: indique le limiter si present.
- Pour Route::apiResource, les 5 routes REST standards sont deployeees explicitement ci-dessous.

## 1) Routes web

- GET / -> redirection 302 vers /app/
- GET /app/{any?} -> sert public/app/index.html (SPA)

## 2) Channels broadcast

- channel chat.user.{id}
  - acces: utilisateur authentifie dont id_utilisateur == {id}
- channel chat.conversation.{conversationId}
  - acces: utilisateur participant a la conversation

## 3) Routes API explicites

## 3.1 Auth, compte, email, reset

- POST /api/login
  - Auth: non
  - Throttle: auth-login
  - Controleur: UserController@login

- POST /api/logout
  - Auth: oui
  - Controleur: UserController@logout

- GET /api/me
  - Auth: oui
  - Controleur: UserController@me

- GET /api/email/verify/{id}/{hash}
  - Auth: non (middleware signed)
  - Throttle: email-verification
  - Controleur: UserController@verifyEmail

- POST /api/email/verification-notification
  - Auth: non (dans routes)
  - Throttle: email-verification
  - Controleur: UserController@resendVerificationEmail

- POST /api/password-reset/request
  - Auth: non
  - Throttle: password-reset
  - Controleur: PasswordResetController@requestReset

- POST /api/password-reset/verify
  - Auth: non
  - Throttle: password-reset
  - Controleur: PasswordResetController@verifyToken

- POST /api/password-reset/reset
  - Auth: non
  - Throttle: password-reset
  - Controleur: PasswordResetController@resetPassword

## 3.2 Notifications

- GET /api/notifications
  - Auth: oui
  - Throttle: notifications-read
  - Controleur: NotificationController@index

- POST /api/notifications/read
  - Auth: oui
  - Throttle: notifications-read
  - Controleur: NotificationController@markRead

## 3.3 Chat

- GET /api/conversations
  - Auth: oui
  - Controleur: ChatConversationController@index

- POST /api/conversations/direct
  - Auth: oui
  - Controleur: ChatConversationController@storeDirect

- POST /api/conversations/mission
  - Auth: oui
  - Controleur: ChatConversationController@storeOrGetMissionGroup

- POST /api/conversations/{conversationId}/participants
  - Auth: oui
  - Controleur: ChatConversationController@addParticipant

- GET /api/conversations/{conversationId}/messages
  - Auth: oui
  - Controleur: ChatMessageController@index

- POST /api/conversations/{conversationId}/messages
  - Auth: oui
  - Controleur: ChatMessageController@store

- POST /api/messages/{messageId}/read
  - Auth: oui
  - Controleur: ChatMessageController@markRead

## 3.4 Cartographie / localisation

- POST /api/maps/resolve
  - Auth: non
  - Throttle: maps-resolve
  - Controleur: MapsController@resolve

- GET /api/missions/{id}/positions
  - Auth: oui
  - Controleur: MissionPositionController@index

- POST /api/missions/{id}/positions
  - Auth: oui
  - Controleur: MissionPositionController@store

- POST /api/location
  - Auth: oui
  - Controleur: LocationController@updateLocation

- GET /api/location
  - Auth: oui
  - Controleur: LocationController@getLocation

- DELETE /api/location
  - Auth: oui
  - Controleur: LocationController@deleteLocation

- GET /api/locations
  - Auth: oui
  - Controleur: LocationController@getAllLocations

## 3.5 Favoris / stats

- GET /api/favorites
  - Auth: oui
  - Controleur: FavoriteController@index

- POST /api/favorites/{missionId}
  - Auth: oui
  - Controleur: FavoriteController@store

- DELETE /api/favorites/{missionId}
  - Auth: oui
  - Controleur: FavoriteController@destroy

- GET /api/favorites/{missionId}/check
  - Auth: oui
  - Controleur: FavoriteController@check

- GET /api/stats
  - Auth: oui
  - Controleur: StatsController@index

- GET /api/stats/me
  - Auth: oui
  - Controleur: StatsController@me

## 3.6 Urgences

- POST /api/missions/{idMission}/urgences
  - Auth: oui
  - Throttle: emergency-actions
  - Controleur: MissionEmergencyMessageController@storeForMission

- GET /api/urgences
  - Auth: oui
  - Throttle: emergency-actions
  - Controleur: MissionEmergencyMessageController@index

- POST /api/urgences/{idUrgence}/consultation
  - Auth: oui
  - Throttle: emergency-actions
  - Controleur: MissionEmergencyMessageController@markViewed

- POST /api/urgences/{idUrgence}/prise-en-charge
  - Auth: oui
  - Throttle: emergency-actions
  - Controleur: MissionEmergencyMessageController@takeOwnership

## 3.7 Inscriptions metier

- POST /api/missions/{idMission}/inscriptions
  - Auth: oui
  - Controleur: PostulationController@inscrireMission

- POST /api/evenements/{idEvenement}/inscriptions
  - Auth: oui
  - Controleur: PostulationController@inscrireEvenement

- POST /api/missions/{missionId}/replace-volunteer
  - Auth: oui
  - Controleur: AffectationController@replaceVolunteer

- PATCH /api/missions/{id}/responsable
  - Auth: non (dans routes)
  - Controleur: MissionController@assignResponsable

## 4) Routes API derivees de apiResource

## 4.1 users (UserController)

- GET /api/users
- POST /api/users
- GET /api/users/{user}
- PUT/PATCH /api/users/{user}
- DELETE /api/users/{user}

Routes associees users (hors resource):
- GET /api/users/{id}/competences
- POST /api/users/{id}/competences
- DELETE /api/users/{id}/competences/{competenceId}
- GET /api/users/{id}/badges
- POST /api/users/{id}/badges
- DELETE /api/users/{id}/badges/{badgeId}

## 4.2 admins (AdminController)

- GET /api/admins
- POST /api/admins
- GET /api/admins/{admin}
- PUT/PATCH /api/admins/{admin}
- DELETE /api/admins/{admin}

## 4.3 affectations (AffectationController)

- GET /api/affectations
- POST /api/affectations
- GET /api/affectations/{affectation}
- PUT/PATCH /api/affectations/{affectation}
- DELETE /api/affectations/{affectation}

Auth: oui (middleware sur apiResource)

## 4.4 badges (BadgeController)

- GET /api/badges
- POST /api/badges
- GET /api/badges/{badge}
- PUT/PATCH /api/badges/{badge}
- DELETE /api/badges/{badge}

## 4.5 benevoles (BenevoleController)

- GET /api/benevoles
- POST /api/benevoles
- GET /api/benevoles/{benevole}
- PUT/PATCH /api/benevoles/{benevole}
- DELETE /api/benevoles/{benevole}

## 4.6 certificats (CertificatController)

- GET /api/certificats
- POST /api/certificats
- GET /api/certificats/{certificat}
- PUT/PATCH /api/certificats/{certificat}
- DELETE /api/certificats/{certificat}

Route associee:
- GET /api/certificats/{id}/download

Auth: oui (resource + download)

## 4.7 competences (CompetenceController)

- GET /api/competences
- POST /api/competences
- GET /api/competences/{competence}
- PUT/PATCH /api/competences/{competence}
- DELETE /api/competences/{competence}

## 4.8 courses alias (EvenementController)

- GET /api/courses
- POST /api/courses
- GET /api/courses/{course}
- PUT/PATCH /api/courses/{course}
- DELETE /api/courses/{course}

Note: alias legacy vers EvenementController.

## 4.9 documents (DocumentController)

- GET /api/documents
- POST /api/documents
- GET /api/documents/{document}
- PUT/PATCH /api/documents/{document}
- DELETE /api/documents/{document}

## 4.10 evenements (EvenementController)

- GET /api/evenements
- POST /api/evenements
- GET /api/evenements/{evenement}
- PUT/PATCH /api/evenements/{evenement}
- DELETE /api/evenements/{evenement}

## 4.11 missions (MissionController)

- GET /api/missions
- POST /api/missions
- GET /api/missions/{mission}
- PUT/PATCH /api/missions/{mission}
- DELETE /api/missions/{mission}

## 4.12 postulations (PostulationController)

- GET /api/postulations
- POST /api/postulations
- GET /api/postulations/{postulation}
- PUT/PATCH /api/postulations/{postulation}
- DELETE /api/postulations/{postulation}

Auth: oui (middleware sur apiResource)

## 4.13 telephones (TelephoneController)

- GET /api/telephones
- POST /api/telephones
- GET /api/telephones/{telephone}
- PUT/PATCH /api/telephones/{telephone}
- DELETE /api/telephones/{telephone}

## 5) Notes de redaction pour le manuel

- Bien distinguer:
  - routes web (serving SPA)
  - routes API (metier)
  - channels broadcast (temps reel)
- Mentionner explicitement les routes sensibles protegees par auth:sanctum.
- Quand une route n a pas auth dans routes/api.php, le signaler factuellement (sans conclure automatiquement a une erreur metier).
