<?php
// Автор: Борис Гусев (bgus.ru)
// Версия: 1.2 - Добавлена возможность указывать свой класс для каждой ссылки

session_start();

if (isset($_SESSION['used_tokens'])) {
    $_SESSION['used_tokens'] = array_filter($_SESSION['used_tokens'], fn($t) => $t > time() - 300);
}
if (isset($_SESSION['valid_tokens'])) {
    $_SESSION['valid_tokens'] = array_filter($_SESSION['valid_tokens'], fn($t) => $t > time() - 300);
}

// === КОНФИГ === //
$files = [
    'grifmaster_price' => [
        'path' => '/var/www/u1262151/data/www/data/down/files/dq9/price_file_default.xlsx',
        'name' => 'Прайс GRIFMASTER',
        'disabled' => false,
        'hide' => false,
        'show_confirmation' => true, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => false, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p>Вы загружаете актуальный прайс-лист GRIFMASTER в формате Excel.</p>
            <p><b>Обратите внимание:</b> для удобного просмотра с мобильных устройств вы можете воспользоваться <a href="https://grifmaster.ru/files/?f=web_price">веб-версией прайса</a>.</p>
        ',
        'confirm_button_text' => 'Скачать в Excel',
        'cancel_button_text' => 'Отмена',
        'icon' => '<i class="fas fa-file-excel"></i>', // Иконка для Excel
        'class' => 'btn-dark' // Добавляем класс для этой ссылки
    ],
    'kerama_price' => [
        'path' => '/var/www/u1262151/data/www/data/down/files/dq9/price_file_kerama.xlsx',
        'name' => 'Прайс Kerama Marazzi',
        'disabled' => false,
        'hide' => false,
        'show_confirmation' => true, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => true, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p>Уважаемые партнеры GRIFMASTER!</p>
            <p>Обращаем ваше внимание, что условия продажи и доставки продукции Kerama Marazzi имеют региональные ограничения. Это связано с эксклюзивными договоренностями, которые позволяют нам предлагать вам этот востребованный бренд.</p>
            <p>Территории, на которых осуществляется продажа и доставка Kerama Marazzi. Пожалуйста, убедитесь, что ваш регион доставки входит в список:
            <strong>Москва и область, Самарская, Ульяновская, Нижегородская, Владимирская, Ярославская, Костромская, Вологодская, Ивановская, Тверская, Кировская, Рязанская, Калужская области, а также Чувашская Республика, Республики Марий ЭЛ, Татарстан, Удмуртия и Коми.</strong></p>
            <p>Выдача заказов в салоне на Ленинском проспекте города Москвы по данному бренду не осуществляется.</p>
            <p>Вы загружаете актуальный Excel-прайс бренда <b>Kerama Marazzi</b>.</p>',
        'confirm_button_text' => 'Ознакомлен(а), скачать',
        'cancel_button_text' => 'Отмена',
        'icon' => '<i class="fas fa-file-excel"></i>', // Иконка для Excel
        'class' => 'btn-dark' // Добавляем другой класс для этой ссылки
    ],
    'action_price' => [
        'path' => '/var/www/u1262151/data/www/data/down/files/dq9/price_file_action.xlsx',
        'name' => 'Акция НЕЧЕРНАЯ пятница',
        'disabled' => true,
        'hide' => true,
        'show_confirmation' => true, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => true, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p><strong>Уважаемые партнёры GRIFMASTER!</strong></p>
            <p>Данный прайс-лист относится к специальной акции <b>«НЕЧЕРНАЯ пятница — БЕЛАЯ прибыль»</b> и содержит товары со скидками на ассортимент <b>Paini (PURO ITALIANO)</b> в цвете «Хром».</p>
            <p>Акция проводится в период с <b>01.11.2025 по 30.11.2025</b>. Специальное предложение не распространяется на товары уже имеющие скидку или учавствующие в других акциях. Условия предоставления скидок и наличие товара уточняйте у вашего менеджера.</p>
            <p>Для актуализации остатков и получения подтверждения заказа рекомендуем использовать личный кабинет или связаться с вашим менеджером.</p>
            <p>Вы скачиваете актуальный <b>Excel-прайс</b> с акционными позициями бренда <b>Paini</b>.</p>
        ',
        'confirm_button_text' => 'Ознакомлен(а), скачать',
        'cancel_button_text' => 'Отмена',
        'icon' => '<i class="fas fa-file-excel"></i>', // Иконка для Excel
        'class' => 'btn-gradient' // Добавляем другой класс для этой ссылки
    ],
    'web_price' => [
        'path' => '/var/www/u1262151/data/www/data/down/files/dq9/html_price/index.html',
        'name' => 'Общий прайс (веб-версия)',
        'disabled' => false,
        'hide' => false,
        'open_in_browser' => true,
        'icon' => '<i class="fas fa-file-code"></i>', // Иконка для HTML
        'class' => 'btn-dark' // Добавляем ещё один класс
    ],
    'noimage_price' => [
        'path' => '/var/www/u1262151/data/www/data/down/files/ostatki-ppj-0220-light.XLSX',
        'name' => 'Прайс без картинок',
        'disabled' => false,
        'hide' => true,
        'show_confirmation' => true, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => false, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p>Вы загружаете <b>Excel-прайс-лист GRIFMASTER без картинок</b>. Вся необходимая информация доступна в полном объеме.</p>
            <p>В прайсе содержится бренд Kerama Marazzi, условия продажи и доставки продукции которого имеют региональные ограничения.</p>
            <p>Территории, на которых осуществляется продажа и доставка Kerama Marazzi: Москва и область, Самарская, Ульяновская, Нижегородская, 
            Владимирская, Ярославская, Костромская, Вологодская, Ивановская, Тверская, Кировская, Рязанская, Калужская области, 
            а также Чувашская Республика, Республики Марий ЭЛ, Татарстан, Удмуртия и Коми.</p>
            <p>Выдача заказов в салоне на Ленинском проспекте города Москвы по данному бренду не осуществляется.</p>
    ',
        'confirm_button_text' => 'Понятно, скачать',
        'cancel_button_text' => 'Отмена',
        'open_in_browser' => false,
        'icon' => '<i class="fas fa-file-excel"></i>', // Иконка для HTML
        'class' => 'btn-outline-secondary' // Добавляем ещё один класс
        ],
    'grifmaster-catalog' => [
        'path' => '/var/www/u1262151/data/www/shop/wa-data/public/site/pages/kabinet/presentations/katalog_grifmaster_compressed.pdf',
        'name' => 'Презентация каталога GRIFMASTER (full-quality)',
        'disabled' => false,
        'hide' => true,
        'show_confirmation' => false, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => false, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p>Вы загружаете презентацию компании и каталога GRIFMASTER в формате PDF.<br>
            Это оптимизированная версия каталога, размер файла всего: <b>6 МБ</b>.
            <p>Eсли вам нужен каталов в высочайшем качестве, скачать его можно 
            <a target="_blank" href="https://grifmaster.ru/files/?f=grifmaster-catalog-fq">по этой ссылке</a>.<br>Размер файла: <b>651 МБ</b>.</p>
        ',
        'confirm_button_text' => 'Скачать в PDF',
        'cancel_button_text' => 'Отмена',
        'open_in_browser' => false,
        'icon' => '<i class="fas fa-file-excel"></i>', // Иконка для Excel
        'class' => 'btn-dark' // Добавляем класс для этой ссылки
    ],
    'grifmaster-catalog-fq' => [
        'path' => '/var/www/u1262151/data/www/shop/wa-data/public/site/pages/kabinet/presentations/katalog_grifmaster.pdf',
        'name' => 'Презентация каталога GRIFMASTER',
        'disabled' => false,
        'hide' => true,
        'show_confirmation' => false, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => false, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p>Вы загружаете каталог продукции GRIFMASTER в формате PDF.</p>
            <p>Это оптимизированная версия каталога, размер файла всего: <b>6 МБ</b>.</p>
            <p><b>Обратите внимание:</b> если вам нужен каталов в высочайшем качестве, скачать его можно 
            <a target="_blank" href="https://grifmaster.ru/files/?f=grifmaster-catalog">по этой ссылке</a>. Размер файла: <b>651 МБ</b>.</p>
        ',
        'confirm_button_text' => 'Скачать в PDF',
        'cancel_button_text' => 'Отмена',
        'open_in_browser' => false,
        'icon' => '<i class="fas fa-file-excel"></i>', // Иконка для Excel
        'class' => 'btn-dark' // Добавляем класс для этой ссылки
    ],
      'price_ds50' => [
        'path' => '/var/www/u1262151/data/www/data/down/files/dq9/price_file_discount_50.xlsx',
        'name' => 'Акция: Прайс скидка 50%',
        'disabled' => false,
        'hide' => true,
        'show_confirmation' => false, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => false, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p>Вы загружаете актуальный прайс-лист GRIFMASTER в формате Excel.</p>
            <p><b>Обратите внимание:</b> для удобного просмотра с мобильных устройств вы можете воспользоваться <a href="https://grifmaster.ru/files/?f=web_price">веб-версией прайса</a>.</p>
        ',
        'confirm_button_text' => 'Скачать в Excel',
        'cancel_button_text' => 'Отмена',
        'icon' => '<i class="fas fa-file-excel"></i>',
        'class' => 'btn-dark'
    ],
      'price_ds70' => [
        'path' => '/var/www/u1262151/data/www/data/down/files/dq9/price_file_discount_70.xlsx',
        'name' => 'Акция: Прайс скидка 70%',
        'disabled' => false,
        'hide' => true,
        'show_confirmation' => false, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => false, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p>Вы загружаете актуальный прайс-лист GRIFMASTER в формате Excel.</p>
            <p><b>Обратите внимание:</b> для удобного просмотра с мобильных устройств вы можете воспользоваться <a href="https://grifmaster.ru/files/?f=web_price">веб-версией прайса</a>.</p>
        ',
        'confirm_button_text' => 'Скачать в Excel',
        'cancel_button_text' => 'Отмена',
        'icon' => '<i class="fas fa-file-excel"></i>',
        'class' => 'btn-dark'
    ],
      'price_mtv15' => [
        'path' => '/var/www/u1262151/data/www/data/down/files/dq9/price_file_motivation_15.xlsx',
        'name' => 'Акция: Прайс мотивация 15%',
        'disabled' => false,
        'hide' => true,
        'show_confirmation' => false, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => false, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p>Вы загружаете актуальный прайс-лист GRIFMASTER в формате Excel.</p>
            <p><b>Обратите внимание:</b> для удобного просмотра с мобильных устройств вы можете воспользоваться <a href="https://grifmaster.ru/files/?f=web_price">веб-версией прайса</a>.</p>
        ',
        'confirm_button_text' => 'Скачать в Excel',
        'cancel_button_text' => 'Отмена',
        'icon' => '<i class="fas fa-file-excel"></i>',
        'class' => 'btn-dark'
    ],
      'price_mtv20' => [
        'path' => '/var/www/u1262151/data/www/data/down/files/dq9/price_file_motivation_20.xlsx',
        'name' => 'Акция: Прайс мотивация 20%',
        'disabled' => false,
        'hide' => true,
        'show_confirmation' => false, // Показать окно с текстом предупреждения перед скачиванием?
        'require_accept' => false, // Обязывать принять условия перед скачиванием? Запрещает переход по прямой ссылке без ознакомления с текстом
        'confirmation_content' => '
            <p>Вы загружаете актуальный прайс-лист GRIFMASTER в формате Excel.</p>
            <p><b>Обратите внимание:</b> для удобного просмотра с мобильных устройств вы можете воспользоваться <a href="https://grifmaster.ru/files/?f=web_price">веб-версией прайса</a>.</p>
        ',
        'confirm_button_text' => 'Скачать в Excel',
        'cancel_button_text' => 'Отмена',
        'icon' => '<i class="fas fa-file-excel"></i>',
        'class' => 'btn-dark'
    ]
  ];

// === АЛИАСЫ ДЛЯ ФАЙЛОВ === //
$file_aliases = [
    'excel_price' => 'grifmaster_price', // Алиас 'excel_price' указывает на ключ 'grifmaster_price'
    // Добавьте другие алиасы здесь, если потребуется
];

// === ПАРАМЕТРЫ === //
$key = $_GET['f'] ?? '';
$log_file = __DIR__ . '/logs/downloads.csv';
$timestamp = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

// Таймзона сервера для форматирования дат (Москва)
date_default_timezone_set('Europe/Moscow');

// === ФУНКЦИЯ ЛОГА === //
function log_csv($row) {
    global $log_file;
    file_put_contents($log_file, implode(';', $row) . PHP_EOL, FILE_APPEND);
}

// === ОБРАБОТКА АЛИАСОВ === //
if (isset($file_aliases[$key])) {
    $key = $file_aliases[$key]; // Если передан алиас, используем его реальный ключ
}

// === УНИВЕРСАЛЬНАЯ ФУНКЦИЯ ОТРИСОВКИ ОШИБКИ === //
function error_template($title, $message, $button_text = 'Назад', $button_link = '/', $type = 'warning') {
    $icon = [
        'warning' => 'fa-triangle-exclamation text-warning',
        'danger'  => 'fa-circle-xmark text-danger',
        'info'    => 'fa-circle-info text-primary'
    ][$type] ?? 'fa-circle-info text-secondary';

    echo <<<HTML
<!doctype html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body {
  background: linear-gradient(135deg,#f7f8fa,#e9ecef);
  font-family: -apple-system,BlinkMacSystemFont,"SF Pro Display","Inter","Segoe UI",sans-serif;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.card-error {
  background: rgba(255,255,255,0.8);
  backdrop-filter: blur(25px) saturate(180%);
  border-radius: 24px;
  padding: 2.5rem 2rem;
  max-width: 480px;
  text-align: center;
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
  animation: fadeIn .5s ease both;
}
@keyframes fadeIn {
  0% { opacity: 0; transform: translateY(15px) scale(0.97); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}
h4 {
  font-weight: 600;
  margin-bottom: 0.5rem;
}
p {
  color: #444;
  font-size: 1.1rem;
  margin-bottom: 1.5rem;
}
/* Универсальный стиль кнопок */
.btn-lg {
  border-radius: 100px; /* круглые */
  font-weight: 350;
  letter-spacing: 0.2px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: transform .15s ease, box-shadow .3s ease;
  transition: all 0.3s ease;
}

.btn-lg:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

.btn-gradient {
  background: linear-gradient(90deg,#007bff,#6f42c1,#d63384);
  color: #fff !important;
  border: none;
  border-radius: 100px;
  padding: .7rem 1.4rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.btn-gradient:hover {
  background-position: right center;
  box-shadow: 0 8px 18px rgba(111,66,193,.35);
  transform: translateY(-1px);
}
/* Контурная */
.btn-outline-modern {
  border: 1px solid #001F33;
  color: #001F33;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(10px);
  padding: 0.4rem 1.4rem;

}
.btn-outline-modern:hover {
  border: 1px solid #001F33;
  background: rgba(255,255,255,0.3);
  color: #001F33;
  box-shadow: 0 0 8px rgba(0,0,0,0.1);
  padding: 0.4rem 1.4rem;
}
</style>
</head>
<body>
  <div class="card-error">
    <h4><i class="fas {$icon} me-2"></i>{$title}</h4>
    <p>{$message}</p>
    {$button_html}
  </div>
</body>
</html>
HTML;
    exit;
}

// === УТИЛИТЫ === //
function is_hidden_link(array $info): bool {
    // Поддерживаем все варианты на всякий
    return !empty($info['hidde']) || !empty($info['hidden']) || !empty($info['hide']);
}

function file_ts(string $path): ?int {
    // Чистим кэш stat на этот путь, берём realpath (если есть)
    $rp = is_string($path) ? @realpath($path) : false;
    $p  = $rp ?: $path;
    clearstatcache(true, $p);

    if (!is_file($p)) {
        return null;
    }

    // На Linux "creation time" нет, поэтому mtime — лучший выбор.
    // Иногда filemtime может вернуть false (сетевые диски/права) — подстрахуемся ctime.
    $mt = @filemtime($p);
    if ($mt !== false && $mt > 0) return $mt;

    $ct = @filectime($p);
    return ($ct !== false && $ct > 0) ? $ct : null;
}

function latest_prices_mtime(array $files): ?int {
    $latest = null;
    foreach ($files as $info) {
        $path = $info['path'] ?? null;
        if (!$path) continue;
        $ts = file_ts($path);
        if ($ts && ($latest === null || $ts > $latest)) {
            $latest = $ts;
        }
    }
    return $latest;
}

function format_price_ts(?int $ts): string {
    if (!$ts) return '—';
    // d.m.Y в H:i  (буква "в" — обычный текст)
    return date('d.m.Y \в H:i', $ts);
}


// === ГАРДЫ === //
if ($key === '') {
    // вернём список файлов как раньше (кнопки)
    echo <<<HTML
<!doctype html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Файлы GRIFMASTER</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
:root {
  --glass-bg: rgba(255,255,255,0.75);
  --accent: linear-gradient(90deg,#007bff,#6f42c1,#d63384);
  --text-dark: #111;
  --radius: 24px;
}

body {
  background: linear-gradient(135deg,#f7f8fa,#e9ecef);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: -apple-system,BlinkMacSystemFont,"SF Pro Display","Inter","Segoe UI",sans-serif;
  color: var(--text-dark);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.card-modern {
  border: none;
  border-radius: var(--radius);
  padding: 2.5rem 2rem 2rem;
  background: var(--glass-bg);
  backdrop-filter: blur(25px) saturate(180%);
  box-shadow: 0 12px 40px rgba(0,0,0,0.12);
  max-width: 600px;
  width: 100%;
  text-align: center;
  transition: all 0.3s ease;
}

.card-modern {
  animation: cardFadeIn 0.6s ease both;
}
@keyframes cardFadeIn {
  0% {
    opacity: 0;
    transform: translateY(20px) scale(0.97);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.card-modern h4 {
  font-weight: 700;
  font-size: 1.4rem;
  margin-bottom: 0.8rem;
  color: var(--text-dark);
}

.card-modern p {
  color: #555;
  margin-bottom: 2rem;
}

/* Универсальный стиль кнопок */
.btn-lg {
  border-radius: 100px; /* круглые */
  font-weight: 350;
  letter-spacing: 0.2px;
  padding: 1.2rem 1.4rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: transform .15s ease, box-shadow .3s ease;
  transition: all 0.3s ease;
}

.btn-lg:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

/* Неактивные кнопки */
.btn[disabled] {
  position: relative;
  overflow: visible !important;
  opacity: 0.8;
  filter: grayscale(0.2);
}

/* Маленький бейдж “OFF” */
.btn[disabled]::after {
  content: "Временно недоступен";
  position: absolute;
  top: -5px;
  right: -10px;
  background: #dc3545;
  color: #fff;
  font-size: 0.7rem;
  font-weight: 350;
  letter-spacing: 0.3px;
  padding: 2px 5px;
  border-radius: 30px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}

/* Градиентные */
.btn-gradient {
  background: var(--accent);
  color: #fff !important;
  border: none;
  background-size: 200% 100%;
  background-position: left center;
  transition: background-position .3s ease, transform .15s ease, box-shadow .3s ease;
}
.btn-gradient:hover {
  background-position: right center;
  box-shadow: 0 0 16px rgba(111,66,193,.35);
}

/* Тёмные */
.btn-dark {
  background: #001F33;
  color: #fff !important;
  border: none;
}
.btn-dark:hover {
  background: #3295D1;
  color: #fff !important;
  box-shadow: 0 0 12px rgba(0,0,0,0.25);
}

.btn-dark:hover {
  background: #3295D1;
  color: #fff !important;
  box-shadow: 0 0 12px rgba(0,0,0,0.25);
}

/* Светлая кнопка */
.btn-light {
  background: rgba(245,245,245,0.85);
  color: #222;
  border: none;
}
.btn-light:hover {
  background: rgba(255,255,255,1);
  box-shadow: 0 3px 10px rgba(0,0,0,0.08);
}

/* Серый */
.btn-secondary {
  background: rgba(235,235,235,0.9);
  color: #333 !important;
  border: none;
}
.btn-secondary:hover {
  background: rgba(245,245,245,1);
}

/* Контурная */
.btn-outline-modern {
  border: 1px solid #001F33;
  color: #001F33;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(10px);
  padding: 0.4rem 1.4rem;
}
.btn-outline-modern:hover {
  border: 1px solid #001F33;
  background: rgba(255,255,255,0.3);
  color: #001F33;
  box-shadow: 0 0 8px rgba(0,0,0,0.1);
  padding: 0.4rem 1.4rem;
}

footer {
  font-size: 0.95rem;
  color: #999999;
  margin-top: 2rem;
}
</style>

</head>
<body>
<div class="card-modern">
  <h2>Файлы для скачивания</h2>
HTML;
    // --- Берём время обновления основного файла и показываем общее время обновления для всех прайсов ---
    $mainFile = $files['grifmaster_price']['path'] ?? null;
    $ts = ($mainFile && file_exists($mainFile)) ? @filemtime($mainFile) : null;
    $latestFormatted = htmlspecialchars(format_price_ts($ts));
    echo "<p class='text-muted'>Все прайс-листы обновлены {$latestFormatted}</p>";

    echo '<div class="d-grid gap-3 mb-3">';
    foreach ($files as $code => $info) {
        if (is_hidden_link($info)) {
            // Скрываем из списка, прямой доступ остается
            continue;
        }
        $label = htmlspecialchars($info['name']);
        $icon  = $info['icon'] ?? '<i class="fas fa-file"></i>';
        $btn   = $info['class'] ?? 'btn-gradient';

        if (!empty($info['disabled'])) {
            echo "<button class=\"btn $btn btn-lg disabled\" disabled title=\"Файл временно недоступен\">$icon $label</button>";
            continue;
        }

        echo "<a href=\"?f=$code\" class=\"btn $btn btn-lg\">$icon $label</a>";
    }
    echo <<<HTML
  </div>
  <a href="/" class="btn btn-outline-modern btn-lg mt-4"><i class="fas fa-arrow-left"></i> Вернуться на сайт</a>
<footer>
    <div class="mb-2">
        Представленная информация имеет ознакомительный характер, не является публичной офертой и может быть изменена без предварительного уведомления.
    </div>
    <div>© 2005 - 2025 Grifmaster</div>
</footer>
</div>
 <!-- Yandex.Metrika counter --> <script type="text/javascript">     (function(m,e,t,r,i,k,a){         m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};         m[i].l=1*new Date();         for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}         k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)     })(window, document,'script','https://mc.yandex.ru/metrika/tag.js', 'ym');      ym(47113620, 'init', {webvisor:true, trackHash:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true}); </script>  <!-- /Yandex.Metrika counter -->   
</body>
</html>
HTML;
    exit;
}

if (!isset($files[$key])) {
    http_response_code(404);
    log_csv([$timestamp, $ip, $key, 'INVALID_KEY', '-', $agent]);
    error_template(
        'Некорректная ссылка',
        'Файл не найден. Убедитесь, что вы перешли по корректной ссылке.',
        'Назад к списку',
        '/files/',
        'warning'
    );
}

// === ПОДГОТОВКА ДАННЫХ ФАЙЛА === //
$filepath      = $files[$key]['path'];
if (!empty($files[$key]['disabled'])) {
    error_template(
        'Файл временно недоступен',
        'Данный прайс-лист временно отключён или находится в обновлении. Попробуйте позже.',
        'Назад к списку',
        '/files/',
        'info'
    );
}
$display_name  = $files[$key]['name'];
$extension     = pathinfo($filepath, PATHINFO_EXTENSION);

// === МОДАЛКА ПОДТВЕРЖДЕНИЯ (Apple-style) === //
if (!empty($files[$key]['show_confirmation']) && empty($_GET['confirm'])) {
    $confirmation_content = $files[$key]['confirmation_content'];
    $confirm_button_text  = htmlspecialchars($files[$key]['confirm_button_text'] ?? 'Скачать');
    $cancel_button_text   = htmlspecialchars($files[$key]['cancel_button_text']  ?? 'Отмена');

    // Генерируем уникальный одноразовый токен (nonce)
    $nonce = bin2hex(random_bytes(8));
    $token = md5($key . '|' . date('Y-m-d H:i') . '|' . $nonce);

    // Сохраняем токен в сессию как допустимый (пока не использован)
    if (!isset($_SESSION['valid_tokens'])) $_SESSION['valid_tokens'] = [];
    $_SESSION['valid_tokens'][$token] = time();

    $confirm_link = "?f={$key}&confirm=1&token={$token}";

    echo <<<HTML
<!doctype html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Подтверждение загрузки</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
<style>
:root {
  --glass-bg: rgba(255,255,255,0.82);
  --text-dark: #111;
  --text-muted: #555;
  --accent: linear-gradient(90deg,#007bff,#6f42c1,#d63384);
  --radius: 24px;
}
body {
  background: linear-gradient(135deg,#f7f8fa,#e9ecef);
  font-family: -apple-system,BlinkMacSystemFont,"SF Pro Display","Inter","Segoe UI",sans-serif;
  color: var(--text-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Размер модального окна */
.modal-dialog {
  max-width: 720px !important;
}
@media (min-width: 992px) {
  .modal-dialog {
    max-width: 800px !important;
  }
}

.modal-content {
  border: none;
  border-radius: var(--radius);
  background: var(--glass-bg);
  backdrop-filter: blur(25px) saturate(180%);
  box-shadow: 0 12px 40px rgba(0,0,0,0.12);
  overflow: hidden;
}
.modal-content {
  animation: modalFadeIn 0.5s ease both;
}
@keyframes modalFadeIn {
  0% {
    opacity: 0;
    transform: translateY(15px) scale(0.97);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
.modal-header {
  border: none;
  text-align: center;
  padding-top: 1.8rem;
  background: transparent;
}
.modal-title {
  font-weight: 600;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
}
.modal-body {
  font-size: 1.1rem;
  color: var(--text-muted);
  line-height: 1.6;
  padding: 1.7rem 2rem 1rem;
}
.modal-body b, .modal-body strong {
  color: var(--text-dark);
  font-weight: 600;
}
.modal-footer {
  border: none;
  padding: 1.2rem 1.8rem 1.8rem;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: .7rem;
}
.btn {
  border-radius: 100px;
  font-weight: 350;
  letter-spacing: .2px;
  padding: .7rem 1.4rem;
  transition: all .2s ease;
}
.btn-gradient {
  background: var(--accent);
  background-size: 200% 100%;
  color: #fff !important;
  border: none;
  box-shadow: 0 6px 16px rgba(111,66,193,.25);
}
.btn-gradient:hover {
  background-position: right center;
  box-shadow: 0 8px 24px rgba(111,66,193,.35);
  transform: translateY(-1px);
}
.btn-secondary {
  background: rgba(240,240,240,0.8);
  color: #222 !important;
  border: none;
  backdrop-filter: blur(8px);
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.btn-secondary:hover {
  background: rgba(255,255,255,0.95);
  color: #000 !important;
  box-shadow: 0 3px 10px rgba(0,0,0,0.08);
}

.modal-footer {
  border: none;
  padding: 1.2rem 1.8rem 1.8rem;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: .7rem;
  position: relative;
}
#downloadingMessage {
  position: absolute;
  left: 1.5rem;
  bottom: 2.5rem;
  color: #6c757d;
  display: none;
  white-space: nowrap;
  font-size: 0.9rem;
  opacity: 0;
  transition: opacity 0.6s ease;
}
#downloadingMessage.active {
  display: block;
  opacity: 1;
  animation: blinking-text 1.6s ease-in-out infinite;
}
@keyframes blinking-text {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.35; }
}
</style>
</head>
<body>
<div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-circle-info me-2 text-primary opacity-75"></i>Подтверждение скачивания</h5>
      </div>
      <div class="modal-body">$confirmation_content</div>
        <div class="modal-footer">
        <span id="downloadingMessage">Ожидание загрузки…</span>
        <a href="{$confirm_link}" class="btn btn-gradient" id="confirmDownloadBtn">$confirm_button_text</a>
        <a href="/" class="btn btn-secondary" id="cancelOrCloseBtn">$cancel_button_text</a>
      </div>
    </div>
  </div>
</div>
<div class="modal-backdrop fade show"></div>
<script>
document.getElementById('confirmDownloadBtn').addEventListener('click', function(e){
  e.preventDefault();
  const msg = document.getElementById('downloadingMessage');
  this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Скачивается…';
  this.classList.add('disabled');
  this.style.pointerEvents = 'none';
  msg.classList.add('active');
  document.getElementById('cancelOrCloseBtn').textContent = 'Закрыть';
  // Переходим через 600 мс, чтобы успел появиться эффект
  setTimeout(() => { window.location.href = this.href; }, 600);
});
</script>
</body>
</html>
HTML;
    exit;
}

// === ИМЯ ФАЙЛА ДЛЯ СКАЧИВАНИЯ === //
if (empty($files[$key]['open_in_browser'])) {
    $baseName = preg_replace('/[^A-Za-zА-Яа-я0-9_ -]/u','', $display_name);
    $baseName = str_replace([' ','-'],'_', $baseName);
    $dateTag  = date('Ymd_His');
    $filename = "{$baseName}_{$dateTag}.{$extension}";
} else {
    $filename = $display_name;
}

// === ПРОВЕРКА НАЛИЧИЯ ФАЙЛА === //
if (!file_exists($filepath)) {
    http_response_code(404);
    log_csv([$timestamp, $ip, $key, 'FILE_NOT_FOUND', $filepath, $agent]);
    error_template(
        'Файл не найден',
        "Файл <strong>{$display_name}</strong> отсутствует на сервере или временно недоступен.",
        'Вернуться',
        '/files/',
        'danger'
    );
}

// === ОТКРЫТИЕ В БРАУЗЕРЕ ИЛИ СКАЧИВАНИЕ === //
if (!empty($files[$key]['open_in_browser'])) {
    log_csv([$timestamp, $ip, $key, 'VIEWED_HTML', $filepath, $agent]);
    header('Content-Type: text/html; charset=UTF-8');
    readfile($filepath);
    exit;
}

// === Проверка обязательного подтверждения === //
if (!empty($files[$key]['require_accept'])) {
    $token_ok = false;
    $token = $_GET['token'] ?? '';

    if (!isset($_SESSION['valid_tokens'])) $_SESSION['valid_tokens'] = [];
    if (!isset($_SESSION['used_tokens']))  $_SESSION['used_tokens']  = [];

    // Проверка: токен существует и ещё не использован
    if ($token && isset($_SESSION['valid_tokens'][$token]) && !isset($_SESSION['used_tokens'][$token])) {
        $token_ok = true;
    }

    if (empty($_GET['confirm']) || !$token_ok) {
        $msg = !$token ? 
            'Перед скачиванием этого файла необходимо ознакомиться с условиями.' :
            'Ссылка на скачивание уже использована или недействительна. Ознакомьтесь с условиями, чтобы скачать файл снова.';

        $msg .= '<br><br>
            <a href="/files/?f=' . htmlspecialchars($key) . '" class="btn btn-gradient mt-3">
                <i class="fas fa-circle-info me-2"></i>Ознакомиться и скачать
            </a>
            <br>
            <a href="/files/" class="btn btn-outline-modern btn-lg mt-4">
                <i class="fas fa-arrow-left"></i> К файлам
            </a>';

        error_template('Требуется подтверждение', $msg, false);
    }
}

log_csv([$timestamp, $ip, $key, 'SUCCESS', $filepath, $agent]);

// === Отмечаем токен использованным до отправки файла === //
if (!empty($files[$key]['require_accept']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    if (session_status() === PHP_SESSION_NONE) session_start();

    // отмечаем токен использованным
    $_SESSION['used_tokens'][$token] = time();

    // удаляем из допустимых
    unset($_SESSION['valid_tokens'][$token]);

    // чистим старые
    $_SESSION['used_tokens'] = array_filter($_SESSION['used_tokens'], fn($t) => $t > time() - 300);

    session_write_close();
}

// === Отправка файла === //
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

readfile($filepath);
exit;
?>