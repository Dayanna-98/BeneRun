<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BenevoleController;
use App\Http\Controllers\CertificatController;
use App\Http\Controllers\ChatConversationController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\CompetenceController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MapsController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MissionEmergencyMessageController;
use App\Http\Controllers\MissionPositionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PostulationController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TelephoneController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [UserController::class, 'login'])->middleware('throttle:auth-login');
Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:email-verification'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [UserController::class, 'resendVerificationEmail'])
    ->middleware('throttle:email-verification');

// Réinitialisation de mot de passe (routes publiques)
Route::post('/password-reset/request', [PasswordResetController::class, 'requestReset'])->middleware('throttle:password-reset');
Route::post('/password-reset/verify', [PasswordResetController::class, 'verifyToken'])->middleware('throttle:password-reset');
Route::post('/password-reset/reset', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:password-reset');

// Google Maps URL resolution (public route)
Route::post('/maps/resolve', [MapsController::class, 'resolve'])->middleware('throttle:maps-resolve');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/notifications', [NotificationController::class, 'index'])->middleware('throttle:notifications-read');
    Route::post('/notifications/read', [NotificationController::class, 'markRead'])->middleware('throttle:notifications-read');

    // Messagerie persistante
    Route::get('/conversations', [ChatConversationController::class, 'index']);
    Route::post('/conversations/direct', [ChatConversationController::class, 'storeDirect']);
    Route::post('/conversations/mission', [ChatConversationController::class, 'storeOrGetMissionGroup']);
    Route::post('/conversations/{conversationId}/participants', [ChatConversationController::class, 'addParticipant']);
    Route::get('/conversations/{conversationId}/messages', [ChatMessageController::class, 'index']);
    Route::post('/conversations/{conversationId}/messages', [ChatMessageController::class, 'store']);
    Route::post('/messages/{messageId}/read', [ChatMessageController::class, 'markRead']);
});
Route::get('/users/{id}/competences', [UserController::class, 'competences']);
Route::post('/users/{id}/competences', [UserController::class, 'addCompetence']);
Route::delete('/users/{id}/competences/{competenceId}', [UserController::class, 'removeCompetence']);
Route::get('/users/{id}/badges', [UserController::class, 'badges']);
Route::post('/users/{id}/badges', [UserController::class, 'addBadge']);
Route::delete('/users/{id}/badges/{badgeId}', [UserController::class, 'removeBadge']);
Route::apiResource('/users', UserController::class);
Route::apiResource('/admins', AdminController::class); // ->middleware('auth:sanctum');
Route::apiResource('/affectations', AffectationController::class)->middleware('auth:sanctum');
Route::post('/missions/{missionId}/replace-volunteer', [AffectationController::class, 'replaceVolunteer'])
    ->middleware('auth:sanctum');
Route::apiResource('/badges', BadgeController::class); // ->middleware('auth:sanctum');
Route::apiResource('/benevoles', BenevoleController::class); // ->middleware('auth:sanctum');
Route::get('/certificats/{id}/download', [CertificatController::class, 'download'])->middleware('auth:sanctum');
Route::apiResource('/certificats', CertificatController::class)->middleware('auth:sanctum');
Route::apiResource('/competences', CompetenceController::class); // ->middleware('auth:sanctum');
Route::apiResource('/courses', EvenementController::class); // ancien alias conservé pour compatibilité
Route::apiResource('/documents', DocumentController::class); // ->middleware('auth:sanctum');
Route::apiResource('/evenements', EvenementController::class); // ->middleware('auth:sanctum');
Route::apiResource('/missions', MissionController::class); // ->middleware('auth:sanctum');
Route::patch('/missions/{id}/responsable', [MissionController::class, 'assignResponsable']);
Route::post('/missions/{idMission}/inscriptions', [PostulationController::class, 'inscrireMission'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/missions/{id}/positions', [MissionPositionController::class, 'index']);
    Route::post('/missions/{id}/positions', [MissionPositionController::class, 'store']);
});
Route::middleware('auth:sanctum')->group(function () {
    // Favoris
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{missionId}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{missionId}', [FavoriteController::class, 'destroy']);
    Route::get('/favorites/{missionId}/check', [FavoriteController::class, 'check']);
    // Statistiques
    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/stats/me', [StatsController::class, 'me']);
    // Localisation en temps réel
    Route::post('/location', [LocationController::class, 'updateLocation']);
    Route::get('/location', [LocationController::class, 'getLocation']);
    Route::delete('/location', [LocationController::class, 'deleteLocation']);
    Route::get('/locations', [LocationController::class, 'getAllLocations']);
});
Route::post('/missions/{idMission}/urgences', [MissionEmergencyMessageController::class, 'storeForMission'])
    ->middleware(['auth:sanctum', 'throttle:emergency-actions']);
Route::post('/evenements/{idEvenement}/inscriptions', [PostulationController::class, 'inscrireEvenement'])->middleware('auth:sanctum');
Route::get('/urgences', [MissionEmergencyMessageController::class, 'index'])
    ->middleware(['auth:sanctum', 'throttle:emergency-actions']);
Route::post('/urgences/{idUrgence}/consultation', [MissionEmergencyMessageController::class, 'markViewed'])
    ->middleware(['auth:sanctum', 'throttle:emergency-actions']);
Route::post('/urgences/{idUrgence}/prise-en-charge', [MissionEmergencyMessageController::class, 'takeOwnership'])
    ->middleware(['auth:sanctum', 'throttle:emergency-actions']);
Route::apiResource('/postulations', PostulationController::class)->middleware('auth:sanctum');
Route::apiResource('/telephones', TelephoneController::class); // ->middleware('auth:sanctum');
