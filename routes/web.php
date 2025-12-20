<?php

/**
 * Название: web.php (Маршруты веб-интерфейса)
 * Дата-время: 20-12-2025 23:10
 * Описание: Главный файл маршрутизации приложения. Управляет доступом 
 * к публичным страницам и защищенной области B2B кабинета.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;

/**
 * Название: Базовые редиректы
 * Дата-время: 20-12-2025 21:45
 * Описание: Перенаправление с корня сайта на страницу логина.
 */
Route::get('/', function () {
    return redirect()->route('login');
});

/**
 * Название: Группа маршрутов Guest (Гости)
 * Дата-время: 20-12-2025 23:10
 * Описание: Маршруты регистрации и входа. Доступны только неавторизованным пользователям.
 */
Route::middleware('guest')->group(function () {
    
    // Авторизация
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Регистрация и верификация (твоя логика с 6-значным кодом)
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register_action', [RegisterController::class, 'register'])->name('register.post');
    Route::get('/verify', [RegisterController::class, 'showVerify'])->name('register.verify');
    Route::post('/verify_action', [RegisterController::class, 'verify'])->name('register.verify.post');
});

/**
 * Название: Группа маршрутов Auth (Авторизованные)
 * Дата-время: 20-12-2025 23:10
 * Описание: Защищенная область. Включает проверку заполненности профиля через Middleware.
 */
Route::middleware(['auth', 'check.profile'])->group(function () {
    
    // Главный экран партнера (Dashboard)
    Route::get('/dashboard', function () {
        return "Привет, ты внутри системы!"; 
    })->name('dashboard');

    // Маршрут для выхода
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/**
 * Название: Исключения профиля
 * Дата-время: 20-12-2025 23:10
 * Описание: Страница заполнения профиля должна быть доступна авторизованному юзеру, 
 * но не должна проверять саму себя на "заполненность", иначе будет редирект-петля.
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile/complete', function() {
        return view('profile.complete'); 
    })->name('profile.complete');
    
    Route::post('/profile/complete/save', [RegisterController::class, 'saveProfile'])->name('profile.complete.save');
});