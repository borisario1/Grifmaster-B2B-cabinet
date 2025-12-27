<?php

/**
 * Название: DadataService
 * Дата-время: 27-12-2025 21:40
 * Описание: Сервисный класс для общения с внешним API DaData.
 * Адаптация класса Dadata.php (v.1.0.1 by Boris Gusev) под Laravel.
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class DadataService
{
    private string $token;
    private ?string $secret;
    private string $url;

    /**
     * Конструктор. Проверяет наличие конфигурации, как в оригинале.
     * @throws Exception
     */
    public function __construct()
    {
        $this->token = config('services.dadata.token');
        $this->url   = config('services.dadata.base_url');
        $this->secret = config('services.dadata.secret');

        if (empty($this->token) || empty($this->url)) {
            throw new Exception("Dadata не сконфигурирована (отсутствует API KEY или URL).");
        }
    }

    /**
     * Поиск организации по ИНН/ОГРН.
     * Логика полностью повторяет оригинальный метод findByInn.
     *
     * @param string $inn
     * @param string|null $type LEGAL / INDIVIDUAL или null
     * @param int $count
     * @return array suggestions
     * @throws Exception
     */
    public function findByInn(string $inn, ?string $type = null, int $count = 5): array
    {
        // 1. Формируем payload (тело запроса)
        $payload = [
            'query' => $inn,
            'count' => $count
        ];

        if ($type !== null) {
            $payload['type'] = $type;
        }

        // 2. Формируем запрос
        // Laravel Http facade под капотом использует тот же cURL/Guzzle
        $request = Http::timeout(10) // Твой таймаут из оригинала
            ->withoutVerifying()
            ->withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Token ' . $this->token,
            ]);

        // Добавляем секретный ключ, если он есть (как в оригинале)
        if ($this->secret) {
            $request->withHeaders(['X-Secret' => $this->secret]);
        }

        // 3. Выполняем POST запрос
        // JSON_UNESCAPED_UNICODE Laravel делает автоматически
        $response = $request->post($this->url, $payload);

        // 4. Обработка ошибок (аналог проверки $http_code >= 400)
        if ($response->failed()) {
            $data = $response->json();
            $msg = $data['message'] ?? ("HTTP " . $response->status());
            throw new Exception("Ошибка Dadata: " . $msg);
        }

        // 5. Разбор ответа
        $data = $response->json();

        if (!isset($data['suggestions'])) {
            throw new Exception("Некорректный ответ Dadata (нет поля suggestions).");
        }

        return $data['suggestions'];
    }
}