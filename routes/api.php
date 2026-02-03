<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BenevoleController;
use App\Http\Controllers\CertificatController;
use App\Http\Controllers\CompetenceController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\PostulationController;
use App\Http\Controllers\TelephoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('/admin', AdminController::class)->middleware('auth:sanctum');
Route::apiResource('/affectation', AffectationController::class)->middleware('auth:sanctum');
Route::apiResource('/badge', BadgeController::class)->middleware('auth:sanctum');
Route::apiResource('/benevole', BenevoleController::class)->middleware('auth:sanctum');
Route::apiResource('/certificat', CertificatController::class)->middleware('auth:sanctum');
Route::apiResource('/competence', CompetenceController::class)->middleware('auth:sanctum');
Route::apiResource('/course', CourseController::class)->middleware('auth:sanctum');
Route::apiResource('/document', DocumentController::class)->middleware('auth:sanctum');
Route::apiResource('/mission', MissionController::class)->middleware('auth:sanctum');
Route::apiResource('/postulation', PostulationController::class)->middleware('auth:sanctum');
Route::apiResource('/telephone', TelephoneController::class)->middleware('auth:sanctum');