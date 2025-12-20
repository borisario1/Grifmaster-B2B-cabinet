<?php

/**
 * Название: MailService (Сервис отправки почты через SMTP.BZ)
 * Дата-время: 20-12-2025 22:55
 * Описание: Адаптация оригинального Mailer для Laravel 12. 
 * Реализует отправку HTML-писем через API SMTP.BZ с использованием cURL.
 * Включает оптимизации для локальной разработки (IPv4, обход SSL).
 */

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Отправка письма
     * * @param string $to      Email получателя
     * @param string $subject Тема письма
     * @param string $html     HTML-содержимое письма
     * @param string|null $text Текстовая версия (необязательно)
     * @return bool           True при успехе, False при любой ошибке
     */
    public static function send(string $to, string $subject, string $html, ?string $text = null): bool
    {
        // Получение конфигурационных данных из config/services.php
        $url = config('services.smtpbz.url');
        $key = config('services.smtpbz.key');

        // Подготовка данных для multipart/form-data запроса
        $post = [
            'from'    => config('services.smtpbz.from_email'),
            'name'    => config('services.smtpbz.from_name'),
            'to'      => $to,
            'subject' => $subject,
            'html'    => $html,
        ];

        if ($text) {
            $post['text'] = $text;
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post, 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "authorization: " . $key,
                "Accept: application/json"
            ],
            CURLOPT_USERAGENT      => "GrifmasterB2B-Mailer/1.0",
            
            // Настройки таймаутов (критично для стабильности интерфейса)
            CURLOPT_CONNECTTIMEOUT => 5, // Таймаут на установку соединения
            CURLOPT_TIMEOUT        => 8, // Общий таймаут выполнения запроса
            
            // Инженерные правки для локального окружения
            CURLOPT_SSL_VERIFYPEER => false,             // Отключаем проверку SSL (для Localhost)
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4, // Принудительно IPv4 (решает 90% таймаутов)
        ]);

        $response  = curl_exec($ch);
        $curl_err  = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Обработка сетевых ошибок (CURL)
        if ($curl_err) {
            Log::error("SMTP.BZ CURL ERROR: " . $curl_err . " [To: $to]");
            return false;
        }

        // Обработка логических ошибок API
        $json = json_decode($response, true);
        if ($http_code !== 200 || empty($json['result'])) {
            Log::error("SMTP.BZ API ERROR: " . $response . " [HTTP Code: $http_code]");
            return false;
        }

        return true;
    }
}