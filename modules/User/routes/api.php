<?php

use Illuminate\Support\Facades\Route;
use Modules\User\App\Http\Controllers\UserController;

// Everything below requires a valid Sanctum bearer token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [UserController::class, 'me']);
});
