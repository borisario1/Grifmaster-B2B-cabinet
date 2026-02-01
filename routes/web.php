<?php

/**
 * Название: web.php (Маршруты веб-интерфейса)
 * Дата-время: 26-01-2026
 * Описание: Главный файл маршрутизации.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{AuthController, RegisterController, RecoveryPassController};
use App\Http\Controllers\{ProfileController, OrganizationController, DashboardController, NotificationController, TicketController, StoreController, ProductController, CartController, OrderController, FilesController, SecureDownloadController};

/**
 * --------------------------------------------------------------------------
 * Базовые редиректы и универсальные обработчики
 * --------------------------------------------------------------------------
 */
Route::get('/', function () {
    return redirect()->route('login');
});

// Универсальная заплатка для GET-запросов на корзину/действия, которые должны быть POST
Route::get('/cart/add', function() { return redirect()->route('catalog.index'); });
Route::get('/cart/clear', function() { return redirect()->route('cart.index'); });
Route::get('/catalog/checkout', function() { return redirect()->route('cart.index'); });

/**
 * --------------------------------------------------------------------------
 * Группа: GUEST (Гости)
 * --------------------------------------------------------------------------
 */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register_action', [RegisterController::class, 'register'])->name('register.post');
    Route::get('/register_action', function() { return redirect()->route('register'); }); // Заплатка

    Route::get('/verify', [RegisterController::class, 'showVerify'])->name('register.verify');
    Route::post('/verify_action', [RegisterController::class, 'verify'])->name('register.verify.post');

    Route::get('/recovery_pass', [RecoveryPassController::class, 'showRecoveryPass'])->name('recovery.pass');
    Route::post('/recovery_pass', [RecoveryPassController::class, 'sendRecoveryCode'])->name('recovery.send');
    Route::get('/recovery_verify', [RecoveryPassController::class, 'showVerifyForm'])->name('recovery.verify.form');
    Route::post('/recovery_verify', [RecoveryPassController::class, 'verifyAndReset'])->name('recovery.verify.post');
});

/**
 * --------------------------------------------------------------------------
 * Группа: AUTH + CHECK PROFILE
 * --------------------------------------------------------------------------
 */
Route::middleware(['auth', 'check.profile'])->group(function () {
    
    // --- DASHBOARD & SYSTEM ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logoutGet'])->name('logout.get');

    // --- УВЕДОМЛЕНИЯ ---
    Route::post('/profile/notify', [ProfileController::class, 'updateNotification'])->name('profile.notify');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read.all');

    // --- ОРГАНИЗАЦИИ ---
    Route::post('/organizations/lookup', [OrganizationController::class, 'lookup'])->name('organizations.lookup');
    Route::get('/organizations/{organization}/select', [OrganizationController::class, 'select'])->name('organizations.select');
    Route::resource('organizations', OrganizationController::class);

    // --- КАТАЛОГ И ТОВАРЫ ---
    Route::get('/catalog', [StoreController::class, 'index'])
        ->name('catalog.index')
        ->middleware([
            'heavy.throttle:min'
            //'cache.response:5'
        ]);
    Route::post('/catalog/like-do/{id}', [ProductController::class, 'toggleLike'])
        ->name('product.like-do')
        ->middleware('heavy.throttle:min');

    Route::post('/catalog/wishlist-do/{id}', [ProductController::class, 'toggleWishlist'])
        ->name('catalog.wishlist-do')
        ->middleware('heavy.throttle:min');

    Route::get('/catalog/quick-view/{id}', [ProductController::class, 'quickView'])
        ->name('product.quickview')
        ->middleware('heavy.throttle:short');

    Route::post('/catalog/record-view/{id}', [ProductController::class, 'recordView'])
        ->name('product.record-view')
        ->middleware('heavy.throttle:min');

    Route::get('/catalog/download-images/{id}', [ProductController::class, 'downloadImages'])
        ->name('catalog.download_images')
        ->middleware('heavy.throttle:middle');
    
    Route::get('/catalog/wishlist', [StoreController::class, 'wishlist'])
        ->name('catalog.wishlist')
        ->middleware('heavy.throttle:min');

    // Фильтрованные списки
    Route::get('/catalog/liked', [StoreController::class, 'likedProducts'])->name('catalog.liked');
    Route::get('/catalog/ordered', [StoreController::class, 'orderedProducts'])->name('catalog.ordered');
    Route::get('/catalog/viewed', [StoreController::class, 'viewedProducts'])->name('catalog.viewed');

    // --- КОРЗИНА И ЗАКАЗ ---
    Route::get('/catalog/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])
    ->name('cart.add')
    ->middleware('heavy.throttle:min');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    
    Route::post('/catalog/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/catalog/success/{code}', [CartController::class, 'success'])->name('cart.success');

    // --- ИСТОРИЯ ЗАКАЗОВ ---
    Route::get('/catalog/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/catalog/order/{code}', [OrderController::class, 'show'])->name('orders.show');

    // --- ФАЙЛОВЫЙ ЦЕНТР ---
    Route::get('/files', [FilesController::class, 'index'])->name('files.index');
    Route::get('/files/download/{id}', [SecureDownloadController::class, 'download'])
        ->name('files.download')
        ->middleware('heavy.throttle:short');

    Route::get('/requests', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/requests/new', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/requests/save', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/requests/view/{code}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/requests/message/{code}', [TicketController::class, 'sendMessage'])->name('tickets.message');
    Route::get('/requests/close/{code}', [TicketController::class, 'close'])->name('tickets.close');
    Route::get('/requests/attachment/{id}', [TicketController::class, 'downloadAttachment'])->name('tickets.attachment');
});

/**
 * --------------------------------------------------------------------------
 * Группа: AUTH ONLY
 * --------------------------------------------------------------------------
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/save', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

/**
 * --------------------------------------------------------------------------
 * Группа: IMPERSONATION (Вход под пользователем)
 * --------------------------------------------------------------------------
 */
// Выход из режима impersonation (требует авторизации web guard)
Route::middleware('auth:web')->get('/impersonate-stop', [\App\Http\Controllers\ImpersonationController::class, 'stop'])
    ->name('impersonate.stop');

// Вход по токену (без auth middleware — токен сам авторизует)
Route::get('/impersonate-login/{token}', [\App\Http\Controllers\ImpersonationController::class, 'loginWithToken'])
    ->name('impersonate.login');

// Промежуточная страница для открытия URL в новом окне
Route::get('/impersonate-redirect', function () {
    return view('impersonate-redirect');
})->name('impersonate.redirect');