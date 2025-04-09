<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas que requieren autenticación
Route::middleware('auth:sanctum')->group(function () {
    // Rutas protegidas
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Ruta del clima
    Route::get('/weather', [WeatherController::class, 'getCurrentWeather']);

    // Rutas para ciudades favoritas
    Route::get('/favorites', [WeatherController::class, 'getFavoriteCities']);
    Route::post('/favoritesAdd', [WeatherController::class, 'addFavoriteCity']);
    Route::delete('/favorites/{id}', [WeatherController::class, 'removeFavoriteCity']);

    // Ruta para historial de búsqueda
    Route::get('/history', [WeatherController::class, 'getSearchHistory']);
});
