<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class ImportProducts extends Command
{
    use \App\Traits\LoggableCommand;

    protected $signature = 'products:import {--log-id= : ID лога запуска}';
    protected $description = 'Импорт товаров из внешнего CSV файла (1С)';

    public function handle()
    {
        $this->initLog();
        
        try {
            $this->logToDb('Ищу файл с данными...');
            $csvUrl = config('b2b.1c_csv_price.csv_source');
            
            $response = Http::withoutVerifying()->timeout(60)->get($csvUrl);
            
            if ($response->failed()) {
                throw new \Exception("Не удалось начать импорт. Код сервера: " . $response->status());
            }

            $this->logToDb('Файл получен. Начинаю разбор...');
            $content = $response->body();
            $rows = explode("\n", $content);
            $totalRows = count($rows);
            
            $processedArticles = [];
            $created = 0;
            $updated = 0;

            // Задаем максимальный прогресс
            $this->setProgress(0, $totalRows);

            foreach ($rows as $index => $row) {
                // Обновляем прогресс каждые 50 записей или в конце
                if ($index % 50 === 0 || $index === $totalRows - 1) {
                     $this->setProgress($index, $totalRows);
                }

                if ($index === 0 || empty(trim($row))) continue;

                $data = str_getcsv($row, ",");
                $article = $data[3] ?? null;
                $name = $data[1] ?? 'Без названия';
                
                if (!$article) continue;

                $processedArticles[] = $article;

                $product = Product::where('article', $article)->first();
                
                $payload = [
                    'code_1c'          => $data[0] ?? null,
                    'name'             => $name,
                    'free_stock'       => (int)($data[2] ?? 0),
                    'brand'            => $data[4] ?? null,
                    'price'            => (float)($data[6] ?? 0),
                    'status'           => $data[10] ?? null,
                    'product_type'     => $data[11] ?? null,
                    'product_category' => $data[12] ?? null,
                    'collection'       => $data[13] ?? null,
                    'image_filename'   => $data[14] ?? null,
                    'is_active'        => true,
                    'last_synced_at'   => now(),
                ];

                if ($product) {
                    $product->update($payload);
                    $this->logToDb("[UPDATE] {$article}", 'info');
                    $updated++;
                } else {
                    Product::create(array_merge(['article' => $article], $payload));
                    $this->logToDb("[CREATE] {$article}", 'info');
                    $created++;
                }
            }

            $this->logToDb("Импорт завершен. Создано: {$created}. Обновлено: {$updated}.");

            // --- ПРОВЕРКА ПРОПАВШИХ ---
            $toDeactivate = Product::where('is_active', true)
                ->whereNotIn('article', $processedArticles)
                ->get();

            if ($toDeactivate->count() > 0) {
                foreach ($toDeactivate as $item) {
                    $item->update(['is_active' => false]);
                    $this->logToDb("[HIDE] {$item->article}");
                }
                $this->logToDb("Скрыто товаров: " . $toDeactivate->count());
            }

            $this->finishLog(true);

        } catch (\Exception $e) {
            $this->logToDb("Error: " . $e->getMessage(), 'error');
            $this->finishLog(false, $e->getMessage());
        }
    }
}