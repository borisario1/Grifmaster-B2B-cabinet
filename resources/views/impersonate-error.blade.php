<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fecaca;
        }
        .container {
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 400px;
        }
        .icon { font-size: 64px; margin-bottom: 20px; }
        h1 { font-size: 24px; margin-bottom: 12px; color: #fef2f2; }
        p { color: #fca5a5; margin-bottom: 24px; line-height: 1.6; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }
        .btn:hover { background: #b91c1c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚠️</div>
        <h1>Ошибка доступа</h1>
        <p>{{ $message ?? 'Что-то пошло не так.' }}</p>
        <button class="btn" onclick="window.close()">Закрыть окно</button>
    </div>
</body>
</html>
