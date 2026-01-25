<?php

/**
 * Название: web.php (Маршруты веб-интерфейса)
 * Дата-время: 26-01-2026
 * Описание: Главный файл маршрутизации. Управляет доступом 
 * к публичным страницам и защищенной области B2B кабинета.
 */

use Illuminate\Support\Facades\Route;

// Импорт контроллеров (Authentication)
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\RecoveryPassController;

// Импорт контроллеров (Main)
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TicketController;

// Импорт контроллеров (Store)
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

/**
 * --------------------------------------------------------------------------
 * Базовые редиректы
 * --------------------------------------------------------------------------
 */
Route::get('/', function () {
    return redirect()->route('login');
});

/**
 * --------------------------------------------------------------------------
 * Группа: GUEST (Гости)
 * Доступно только неавторизованным пользователям
 * --------------------------------------------------------------------------
 */
Route::middleware('guest')->group(function () {
    // Вход
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Регистрация и Верификация
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register_action', [RegisterController::class, 'register'])->name('register.post');
    Route::get('/verify', [RegisterController::class, 'showVerify'])->name('register.verify');
    Route::post('/verify_action', [RegisterController::class, 'verify'])->name('register.verify.post');

    // Восстановление пароля
    Route::get('/recovery_pass', [RecoveryPassController::class, 'showRecoveryPass'])->name('recovery.pass');
    Route::post('/recovery_pass', [RecoveryPassController::class, 'sendRecoveryCode'])->name('recovery.send');
    Route::get('/recovery_verify', [RecoveryPassController::class, 'showVerifyForm'])->name('recovery.verify.form');
    Route::post('/recovery_verify', [RecoveryPassController::class, 'verifyAndReset'])->name('recovery.verify.post');
});

/**
 * --------------------------------------------------------------------------
 * Группа: AUTH + CHECK PROFILE (Основная рабочая зона)
 * Доступно только авторизованным с заполненным профилем
 * --------------------------------------------------------------------------
 */
Route::middleware(['auth', 'check.profile'])->group(function () {
    
    // --- DASHBOARD & SYSTEM ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logoutGet']); // Альтернативный выход через GET

    // --- УВЕДОМЛЕНИЯ ---
    Route::post('/profile/notify', [ProfileController::class, 'updateNotification'])->name('profile.notify'); // Настройки
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read.all');

    // --- ОРГАНИЗАЦИИ ---
    // Важно: AJAX lookup и select ставим ПЕРЕД resource, чтобы не было конфликта с {id}
    Route::post('/organizations/lookup', [OrganizationController::class, 'lookup'])->name('organizations.lookup');
    Route::get('/organizations/{organization}/select', [OrganizationController::class, 'select'])->name('organizations.select');
    Route::resource('organizations', OrganizationController::class);

    // --- КАТАЛОГ И ТОВАРЫ ---
    //Route::get('/store', [StoreController::class, 'index'])->name('catalog.index');
    Route::get('/store', [StoreController::class, 'index'])
        ->name('catalog.index')
        ->middleware('heavy.throttle:medium'); // Ограничение на обновление 30 секунд.

    // Маркетинг (AJAX действия)
    Route::post('/catalog/like/{id}', [ProductController::class, 'toggleLike'])->name('product.like');
    Route::post('/catalog/wishlist/{id}', [ProductController::class, 'toggleWishlist'])->name('catalog.wishlist');
    
    // Инструменты каталога
    Route::get('/catalog/quick-view/{id}', [ProductController::class, 'quickView'])
    ->name('product.quickview')
        ->middleware('heavy.throttle:short'); // Ограничение на обновление 5 секунд

    //Формирование архива с изображениями для товара (стоит ограничение на загрузку, default = 15 sec for user, only auth users)
    Route::get('/catalog/download-images/{id}', [ProductController::class, 'downloadImages'])
    ->name('catalog.download_images')
        ->middleware('heavy.throttle:middle'); // Ограничение на обновление 15 секунд.
    
    // Страница избранного
    //Route::get('/store/wishlist', [StoreController::class, 'wishlist'])->name('wishlist');
    Route::get('/store/wishlist', [App\Http\Controllers\StoreController::class, 'wishlist'])
    ->name('store.wishlist')
    ->middleware('auth');
    
    // --- КОРЗИНА И ЗАКАЗ ---
    Route::get('/store/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    
    Route::post('/store/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/store/success/{code}', [CartController::class, 'success'])->name('cart.success');

    // --- ИСТОРИЯ ЗАКАЗОВ ---
    Route::get('/store/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/store/order/{code}', [OrderController::class, 'show'])->name('orders.show');

    // --- ТЕХПОДДЕРЖКА (TICKETS) ---
    Route::get('/requests', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/requests/new', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/requests/save', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/requests/view/{code}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/requests/message/{code}', [TicketController::class, 'sendMessage'])->name('tickets.message');
    Route::get('/requests/close/{code}', [TicketController::class, 'close'])->name('tickets.close');
});

/**
 * --------------------------------------------------------------------------
 * Группа: AUTH ONLY (Настройка профиля)
 * Доступно авторизованным (даже если профиль не заполнен)
 * --------------------------------------------------------------------------
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/save', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});