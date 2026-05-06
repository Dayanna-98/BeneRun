<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BenevoleController;
use App\Http\Controllers\CertificatController;
use App\Http\Controllers\CompetenceController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MissionEmergencyMessageController;
use App\Http\Controllers\MissionPositionController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PostulationController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TelephoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [UserController::class, 'login']);

// Réinitialisation de mot de passe (routes publiques)
Route::post('/password-reset/request', [PasswordResetController::class, 'requestReset']);
Route::post('/password-reset/verify', [PasswordResetController::class, 'verifyToken']);
Route::post('/password-reset/reset', [PasswordResetController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/me', [UserController::class, 'me']);
});
Route::get('/users/{id}/competences', [UserController::class, 'competences']);
Route::post('/users/{id}/competences', [UserController::class, 'addCompetence']);
Route::delete('/users/{id}/competences/{competenceId}', [UserController::class, 'removeCompetence']);
Route::get('/users/{id}/badges', [UserController::class, 'badges']);
Route::post('/users/{id}/badges', [UserController::class, 'addBadge']);
Route::delete('/users/{id}/badges/{badgeId}', [UserController::class, 'removeBadge']);
Route::apiResource('/users', UserController::class);
Route::apiResource('/admins', AdminController::class);//->middleware('auth:sanctum');
Route::apiResource('/affectations', AffectationController::class);//->middleware('auth:sanctum');
Route::apiResource('/badges', BadgeController::class);//->middleware('auth:sanctum');
Route::apiResource('/benevoles', BenevoleController::class);//->middleware('auth:sanctum');
Route::apiResource('/certificats', CertificatController::class)->middleware('auth:sanctum');
Route::apiResource('/competences', CompetenceController::class);//->middleware('auth:sanctum');
Route::apiResource('/courses', EvenementController::class);// ancien alias conservé pour compatibilité
Route::apiResource('/documents', DocumentController::class);//->middleware('auth:sanctum');
Route::apiResource('/evenements', EvenementController::class);//->middleware('auth:sanctum');
Route::apiResource('/missions', MissionController::class);//->middleware('auth:sanctum');
Route::patch('/missions/{id}/responsable', [MissionController::class, 'assignResponsable']);
Route::post('/missions/{idMission}/inscriptions', [PostulationController::class, 'inscrireMission']);
Route::get('/missions/{id}/positions', [MissionPositionController::class, 'index']);
Route::post('/missions/{id}/positions', [MissionPositionController::class, 'store']);
Route::get('/notifications', [NotificationController::class, 'index']);
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
Route::post('/missions/{idMission}/urgences', [MissionEmergencyMessageController::class, 'storeForMission']);
Route::post('/evenements/{idEvenement}/inscriptions', [PostulationController::class, 'inscrireEvenement']);
Route::get('/urgences', [MissionEmergencyMessageController::class, 'index']);
Route::post('/urgences/{idUrgence}/consultation', [MissionEmergencyMessageController::class, 'markViewed']);
Route::post('/urgences/{idUrgence}/prise-en-charge', [MissionEmergencyMessageController::class, 'takeOwnership']);
Route::apiResource('/postulations', PostulationController::class);//->middleware('auth:sanctum');
Route::apiResource('/telephones', TelephoneController::class);//->middleware('auth:sanctum');




