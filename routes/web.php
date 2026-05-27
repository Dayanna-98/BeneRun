<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('', 302)->header('Location', '/app/');
});

Route::get('/app/{any?}', function () {
    return file_get_contents(public_path('app/index.html'));
})->where('any', '.*');
