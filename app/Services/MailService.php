<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Отправка письма через API SMTP.BZ
     *
     * @param string $to Email получателя
     * @param string $subject Тема письма
     * @param string $html HTML-содержимое письма
     * @param string|null $text Текстовая версия (необязательно)
     * @return bool
     */
    public static function send(string $to, string $subject, string $html, ?string $text = null): bool
    {
        // Берем конфиги 
        $url = config('b2b.smtpbz.url', 'https://api.smtp.bz/v1/mailer/send');
        $apiKey = config('b2b.smtpbz.key');
        
        // Данные формы (согласно документации formData)
        $payload = [
            'from'    => config('b2b.smtpbz.from_email'),
            'name'    => config('b2b.smtpbz.from_name'),
            'to'      => $to,
            'subject' => $subject,
            'html'    => $html,
        ];

        if ($text) {
            $payload['text'] = $text;
        }

        try {
            $response = Http::withHeaders([
                // Документация: "Ключ необходимо передавать в заголовке Authorization"
                'Authorization' => $apiKey, 
                'Accept'        => 'application/json',
            ])
            ->timeout(8)
            ->connectTimeout(5)
            ->withoutVerifying() // Отключаем SSL (ВРЕМЕННО! Пока идет разработка)
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ])
            ->asForm()
            ->post($url, $payload);

            // 1. Проверяем HTTP статус (200, 400, 401)
            if ($response->failed()) {
                Log::error("SMTP.BZ HTTP ERROR: " . $response->body() . " [Status: " . $response->status() . "]");
                return false;
            }

            // 2. Проверяем логический ответ (в документации написано, что вернется JSON)
            // Обычно, если статус 200, то все ок, но на всякий случай проверяем
            $json = $response->json();

            return true;

        } catch (\Exception $e) {
            Log::error("SMTP.BZ EXCEPTION: " . $e->getMessage());
            return false;
        }
    }
}