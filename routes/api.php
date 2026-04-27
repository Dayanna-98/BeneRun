<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BenevoleController;
use App\Http\Controllers\CertificatController;
use App\Http\Controllers\CompetenceController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\PostulationController;
use App\Http\Controllers\TelephoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [UserController::class, 'login']);
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
Route::apiResource('/courses', CourseController::class);//->middleware('auth:sanctum');
Route::apiResource('/documents', DocumentController::class);//->middleware('auth:sanctum');
Route::apiResource('/evenements', EvenementController::class);//->middleware('auth:sanctum');
Route::apiResource('/missions', MissionController::class);//->middleware('auth:sanctum');
Route::patch('/missions/{id}/responsable', [MissionController::class, 'assignResponsable']);
Route::post('/missions/{idMission}/inscriptions', [PostulationController::class, 'inscrireMission']);
Route::apiResource('/postulations', PostulationController::class);//->middleware('auth:sanctum');
Route::apiResource('/telephones', TelephoneController::class);//->middleware('auth:sanctum');




