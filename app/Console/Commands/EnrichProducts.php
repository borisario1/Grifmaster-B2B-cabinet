<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\WebasystService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnrichProducts extends Command
{
    use \App\Traits\LoggableCommand;

    protected $signature = 'products:enrich 
                            {article? : Артикул конкретного товара} 
                            {--only-new : Обогащать только новые товары (без даты)}
                            {--log-id= : ID лога запуска}';

    protected $description = 'Обогащение товаров данными из Webasyst с выводом отчета';

    public function handle(WebasystService $webasystService)
    {
        $this->initLog();

        $article = $this->argument('article');
        $onlyNew = $this->option('only-new');

        //Проверяем только активные товары из 1С
        $query = Product::where('is_active', true);

        if ($article) {
            $query->where('article', $article);
        }

        if ($onlyNew) {
            $query->whereDoesntHave('details', function($q) {
                $q->whereNotNull('last_enriched_at');
            });
        }

        $totalProducts = $query->count();

        if ($totalProducts === 0) {
            $this->logToDb("Нет товаров для обработки.");
            $this->finishLog(true);
            return 0;
        }

        $this->logToDb("Начинаю работу с {$totalProducts} товарами...");
        
        $enrichedCount = 0;
        $errorCount = 0;
        $processedCount = 0;

        $query->chunkById(50, function ($products) use ($webasystService, &$enrichedCount, &$errorCount, &$processedCount, $totalProducts) {
            foreach ($products as $product) {
                try {
                    $productData = $webasystService->getProductByArticle($product->article);
                    $processedCount++;

                    if ($productData) {
                        $isActive = (isset($productData['status']) && $productData['status'] == 1);
                        $product->update([
                            'is_active' => $isActive,
                            'min_quantity' => isset($productData['order_count_min']) ? (float)$productData['order_count_min'] : $product->min_quantity
                        ]);

                        $product->details()->updateOrCreate(
                            ['product_id' => $product->id],
                            [
                                'summary'     => $productData['summary'] ?? null,
                                'description' => $productData['description'] ?? null,
                                'rating'      => $productData['rating'] ?? 0,
                                'rating_count'=> $productData['rating_count'] ?? 0,
                                'features'    => isset($productData['features']) ? json_encode($productData['features']) : null,
                                'images'      => isset($productData['images']) ? json_encode($productData['images']) : null,
                                'url_slug'    => $productData['frontend_url'] ?? $productData['url'] ?? null,
                                'documents'   => isset($productData['files']) ? json_encode($productData['files']) : null,
                                'last_enriched_at' => now(),
                            ]
                        );
                        $enrichedCount++;
                        
                        // Логируем каждые 15 товаров или последний
                        if ($processedCount % 15 === 0 || $processedCount === $totalProducts) {
                             $this->logToDb("Обработано {$processedCount}/{$totalProducts}. Успешно: {$enrichedCount}");
                             $this->setProgress($processedCount, $totalProducts);
                        }
                    } else {
                        // Не найдено
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->logToDb("Enrich error for {$product->article}: " . $e->getMessage(), 'error');
                }
            }
            // Даем передышку
            usleep(200000); // 0.2s
        });

        $this->logToDb("Итоги: Успешно: {$enrichedCount}, Ошибок: {$errorCount}");
        $this->finishLog(true);

        return 0;
    }
}