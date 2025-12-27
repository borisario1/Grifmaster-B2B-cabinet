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
 * Название: Группа маршрутов Auth (Авторизованные + Проверенные)
 * Описание: Сюда пускаем только тех, кто вошел И заполнил профиль.
 */
Route::middleware(['auth', 'check.profile'])->group(function () {
    Route::get('/dashboard', function () {
            return view('dashboard', [
                'menu' => config('b2b_menu')
            ]);
        })->name('dashboard');
        
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Роут для AJAX переключения (PATCH или POST) - Настройки уведомлений в профиле
    Route::post('/profile/notify', [ProfileController::class, 'updateNotification'])->name('profile.notify');
});

/**
 * Название: Исключения профиля
 * Описание: Доступно всем залогиненным (даже с пустым профилем), 
 * чтобы они могли этот профиль заполнить или подтвердить почту.
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/save', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});