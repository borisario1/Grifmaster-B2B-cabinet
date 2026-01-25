<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebasystService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('b2b.webasyst.url');
        $this->apiKey = config('b2b.webasyst.key');
    }

    /**
     * Сделать запрос к API Webasyst.
     *
     * @param string $method
     * @param array $params
     * @return array|null
     */
    private function request(string $method, array $params = []): ?array
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            Log::error('WebasystService: URL или ключ API не настроены.');
            return null;
        }

        $params['access_token'] = $this->apiKey;
        $params['format'] = 'json';

        $url = rtrim($this->apiUrl, '/') . '/' . $method;

        // ИЗМЕНЕНИЕ 1: Добавлен timeout(30) для решения проблемы с обрывом соединения
        $response = Http::timeout(30)->get($url, $params);

        if ($response->failed()) {
            Log::error('WebasystService: Запрос к API провалился.', [
                'url' => $url,
                'params' => $params,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        
        // Проверка на ошибки от API Webasyst
        if (isset($data['status']) && $data['status'] === 'fail') {
            Log::error('WebasystService: API вернуло ошибку.', [
                 'url' => $url,
                 'params' => $params,
                 'response' => $data,
            ]);
            return null;
        }

        return $data;
    }

    /**
     * Получить данные о товаре по его артикулу (SKU).
     *
     * @param string $article
     * @return array|null
     */
    public function getProductByArticle(string $article): ?array
    {
        // ИЗМЕНЕНИЕ 2: Добавлено поле frontend_url в список полей
        // Также добавил params, где часто лежат доп. данные
        $data = $this->request('shop.product.search', [
            'hash' => 'search/query=' . urlencode($article),
            'fields' => '*,skus,images,features,frontend_url,params'
        ]);

        if (!isset($data['products']) || empty($data['products'])) {
            return null;
        }

        // Поиск возвращает массив товаров. Мы ищем точное совпадение по артикулу.
        foreach ($data['products'] as $product) {
            if (isset($product['skus'])) {
                foreach ($product['skus'] as $sku) {
                    // Нестрогое сравнение, так как артикул может быть числом или строкой
                    if ($sku['sku'] == $article) {
                        return $product;
                    }
                }
            }
        }

        return null;
    }
}