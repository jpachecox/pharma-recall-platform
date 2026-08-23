<?php

use App\Http\Controllers\Api\V1\AlertController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\MedicationController;
use App\Http\Controllers\Api\V1\OrderController;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/medications/search', [MedicationController::class, 'search']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);

        Route::get('/customers/{customer}', [CustomerController::class, 'show']);

        Route::post('/alerts/send', [AlertController::class, 'send']);

        // Sólo para los criterios de aceptación
        //Route::get('/user', function (Request $request) {
        //    return $request->user();
        //});
    });
});
