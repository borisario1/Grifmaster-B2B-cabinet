<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\WebasystService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnrichProducts extends Command
{
    protected $signature = 'products:enrich 
                            {article? : Артикул конкретного товара} 
                            {--only-new : Обогащать только новые товары (без даты)}';

    protected $description = 'Обогащение товаров данными из Webasyst с выводом отчета';

    public function handle(WebasystService $webasystService)
    {
        $article = $this->argument('article');
        $onlyNew = $this->option('only-new');

        $query = Product::query();

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
            $this->warn("Нет товаров для обработки.");
            return 0;
        }

        $this->info("Начинаю работу с {$totalProducts} товарами...");
        $bar = $this->output->createProgressBar($totalProducts);
        
        $enrichedCount = 0;
        $errorCount = 0;
        $notFoundArticles = [];
        
        // Флаг для отладки первого товара
        $debugShown = false;

        $query->chunkById(50, function ($products) use ($webasystService, $bar, &$enrichedCount, &$errorCount, &$notFoundArticles, &$debugShown) {
            foreach ($products as $product) {
                try {
                    $productData = $webasystService->getProductByArticle($product->article);

                    if ($productData) {
                        
                        // --- DEBUG: ВЫВОД СТРУКТУРЫ ПЕРВОГО ТОВАРА ---
                        if (!$debugShown) {
                            $this->line('');
                            $this->info("=== DEBUG DATA FOR: {$product->article} ===");
                            $this->info("ROOT KEYS: " . implode(', ', array_keys($productData)));
                            
                            if (!empty($productData['params'])) {
                                $this->info("PARAMS: " . print_r($productData['params'], true));
                            }
                            // Если files вдруг есть
                            if (isset($productData['files'])) {
                                $this->info("FILES FOUND: " . print_r($productData['files'], true));
                            }
                            $this->info("=== END DEBUG ===");
                            $this->line('');
                            $debugShown = true;
                        }
                        // ---------------------------------------------

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
                                // Пока пишем files, если найдем их в отладке - поправим путь
                                'documents'   => isset($productData['files']) ? json_encode($productData['files']) : null,
                                'last_enriched_at' => now(),
                            ]
                        );

                        if (isset($productData['order_count_min'])) {
                            $product->update(['min_quantity' => (float)$productData['order_count_min']]);
                        }
                        
                        $enrichedCount++;
                    } else {
                        $notFoundArticles[] = $product->article;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    // Логируем ошибку, но не прерываем выполнение
                    Log::error("Enrich error for {$product->article}: " . $e->getMessage());
                }

                $bar->advance();
            }
            // Пауза между чанками, чтобы не задушить API
            sleep(1);
        });

        $bar->finish();
        $this->info("\n\n=== ИТОГИ ===");
        $this->info("Успешно: {$enrichedCount}");
        $this->warn("Ошибок связи: {$errorCount}");
        
        if (!empty($notFoundArticles)) {
            $this->error("Не найдено: " . count($notFoundArticles));
        }

        return 0;
    }
}