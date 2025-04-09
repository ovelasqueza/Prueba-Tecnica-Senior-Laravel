<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WeatherController;

// Rutas de autenticación
Auth::routes();

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
    Route::get('/', [WeatherController::class, 'index'])->name('home');
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
});
