<?php

use App\Http\Controllers\Api\V1\AuthController;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Sólo para los criterios de aceptación
        //Route::get('/user', function (Request $request) {
        //    return $request->user();
        //});
    });
});