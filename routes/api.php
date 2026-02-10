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
use App\Http\Controllers\MissionController;
use App\Http\Controllers\PostulationController;
use App\Http\Controllers\TelephoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/users', UserController::class);
Route::apiResource('/admins', AdminController::class);//->middleware('auth:sanctum');
Route::apiResource('/affectations', AffectationController::class);//->middleware('auth:sanctum');
Route::apiResource('/badges', BadgeController::class);//->middleware('auth:sanctum');
Route::apiResource('/benevoles', BenevoleController::class);//->middleware('auth:sanctum');
Route::apiResource('/certificats', CertificatController::class);//->middleware('auth:sanctum');
Route::apiResource('/competences', CompetenceController::class);//->middleware('auth:sanctum');
Route::apiResource('/courses', CourseController::class);//->middleware('auth:sanctum');
Route::apiResource('/documents', DocumentController::class);//->middleware('auth:sanctum');
Route::apiResource('/missions', MissionController::class);//->middleware('auth:sanctum');
Route::apiResource('/postulations', PostulationController::class);//->middleware('auth:sanctum');
Route::apiResource('/telephones', TelephoneController::class);//->middleware('auth:sanctum');




