<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Открытие личного кабинета...</title>
</head>
<body>
    <script>
        // Открываем URL в новом окне
        const url = new URLSearchParams(window.location.search).get('url');
        if (url) {
            window.open(url, '_blank');
        }
        // Закрываем текущую вкладку или возвращаемся назад
        window.history.back();
    </script>
    <noscript>
        <p>JavaScript отключён. <a href="{{ request()->get('url') }}" target="_blank">Открыть личный кабинет</a></p>
    </noscript>
</body>
</html>
