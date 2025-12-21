<?php

/**
 * Название: web.php (Маршруты веб-интерфейса)
 * Дата-время: 21-12-2025 12:45
 * Описание: Главный файл маршрутизации. Управляет доступом 
 * к публичным страницам и защищенной области B2B кабинета.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;

/**
 * Название: Базовые редиректы
 */
Route::get('/', function () {
    return redirect()->route('login');
});

/**
 * Название: Группа маршрутов Guest (Гости)
 */
Route::middleware('guest')->group(function () {
    // Авторизация
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Регистрация и верификация
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register_action', [RegisterController::class, 'register'])->name('register.post');
    Route::get('/verify', [RegisterController::class, 'showVerify'])->name('register.verify');
    Route::post('/verify_action', [RegisterController::class, 'verify'])->name('register.verify.post');
});


/**
 * Название: Группа маршрутов Auth (Авторизованные)
 * Описание: Защищенная область. Включает проверку заполненности профиля.
 */
Route::middleware(['auth', 'check.profile'])->group(function () {
    
    // Главный экран партнера (Dashboard)
    Route::get('/dashboard', function () {
        $path = base_path('config/b2b_menu.php');
        
        // Проверка файла, чтобы не падать с 500
        $menu = file_exists($path) ? require $path : [];

        return view('dashboard', ['menu' => $menu]);
    })->name('dashboard')->middleware(['auth', 'check.profile']);

    // Маршрут для выхода
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/**
 * Название: Исключения профиля (Auth)
 * Описание: Маршруты для заполнения данных профиля. Доступны всем авторизованным.
 */
Route::middleware('auth')->group(function () {
    // Отображение страницы профиля (где форма заполнения)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    
    // Сохранение данных профиля
    Route::post('/profile/save', [ProfileController::class, 'update'])->name('profile.update');
    
    // Сохранение (смена) пароля
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});