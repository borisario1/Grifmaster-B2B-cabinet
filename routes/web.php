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
use App\Http\Controllers\Auth\RecoveryPassController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProductController;

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

    // Сброс и восстановление пароля
    Route::get('/recovery_pass', [RecoveryPassController::class, 'showRecoveryPass'])->name('recovery.pass');
    Route::post('/recovery_pass', [RecoveryPassController::class, 'sendRecoveryCode'])->name('recovery.send');
    Route::get('/recovery_verify', [RecoveryPassController::class, 'showVerifyForm'])->name('recovery.verify.form');
    Route::post('/recovery_verify', [RecoveryPassController::class, 'verifyAndReset'])->name('recovery.verify.post');
});


/**
 * Название: Группа маршрутов Auth (Авторизованные + Проверенные)
 * Описание: Сюда пускаем только тех, кто вошел И заполнил профиль.
 */
Route::middleware(['auth', 'check.profile'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Если кто-то пытается зайти на logout через строку браузера (GET) - сразу кинем на главную
    // Route::get('/logout', function () {return redirect()->route('dashboard');});

    // Либо этот альт вариант. Мне больше нравится.
    Route::get('/logout', [AuthController::class, 'logoutGet']);

    // Роут для AJAX переключения (PATCH или POST) - Настройки уведомлений в профиле
    Route::post('/profile/notify', [ProfileController::class, 'updateNotification'])->name('profile.notify');
    
    // Уведомления
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read.all');

    // ОРГАНИЗАЦИИ 
    // 1. AJAX поиск (ставим до resource, чтобы не конфликтовало с show)
    Route::post('/organizations/lookup', [OrganizationController::class, 'lookup'])->name('organizations.lookup');
    // 2. Выбор организации (select)
    Route::get('/organizations/{organization}/select', [OrganizationController::class, 'select'])->name('organizations.select');
    // 3. Стандартные действия (index, create, store, destroy, show)
    Route::resource('organizations', OrganizationController::class);

    // Магазин (Каталог)
    Route::get('/store', [App\Http\Controllers\StoreController::class, 'index'])->name('catalog.index');
    
    // МАРКЕТИНГОВЫЕ ФУНКЦИИ
    // Лайк товара (инкремент счетчика)
    Route::post('/catalog/like/{id}', [App\Http\Controllers\ProductController::class, 'toggleLike'])->name('product.like');
    // Данные для модалки быстрого просмотра
    Route::get('/catalog/quick-view/{id}', [App\Http\Controllers\ProductController::class, 'quickView'])->name('product.quickview');
    // Отдаем изображения по товару в ZIP архиве
    Route::get('/catalog/download-images/{id}', [ProductController::class, 'downloadImages'])->name('catalog.download_images');

    // Корзина
    Route::get('/store/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/clear', [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
    
    // Оформление заказа
    Route::post('/store/checkout', [App\Http\Controllers\CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/store/success/{code}', [App\Http\Controllers\CartController::class, 'success'])->name('cart.success');

    // Избранное (чуть позже сделаем контроллер)
    Route::get('/store/wishlist', [App\Http\Controllers\StoreController::class, 'wishlist'])->name('wishlist');

    // Заказы
    Route::get('/store/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/store/order/{code}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');

    // Обращения (Тикеты)
    Route::get('/requests', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/requests/new', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/requests/save', [TicketController::class, 'store'])->name('tickets.store');
    // Route::get('/requests/success/{code}', [TicketController::class, 'success'])->name('tickets.success'); // Используем редирект на show
    Route::get('/requests/view/{code}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/requests/message/{code}', [TicketController::class, 'sendMessage'])->name('tickets.message');
    Route::get('/requests/close/{code}', [TicketController::class, 'close'])->name('tickets.close');
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