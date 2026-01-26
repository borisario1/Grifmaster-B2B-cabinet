<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class ImportProducts extends Command
{
    protected $signature = 'products:import';
    protected $description = 'Импорт товаров из внешнего CSV файла (1С)';

    public function handle()
    {
        $this->info('Ищу файл с данными...');
        $csvUrl = config('b2b.1c_csv_price.csv_source');
        
        try {
            $response = Http::withoutVerifying()->timeout(60)->get($csvUrl);
            
            if ($response->failed()) {
                throw new \Exception("Не удалось начать импорт. Код сервера: " . $response->status());
            }

            $this->info('Файл получен. Начинаю разбор...');
            $content = $response->body();
            $rows = explode("\n", $content);
            
            $processedArticles = [];
            $created = 0;
            $updated = 0;

            foreach ($rows as $index => $row) {
                if ($index === 0 || empty(trim($row))) continue;

                $data = str_getcsv($row, ",");
                $article = $data[3];
                $name = $data[1];
                
                $processedArticles[] = $article;

                // Ищем товар, чтобы понять статус действия
                $product = Product::where('article', $article)->first();
                
                $payload = [
                    'code_1c'          => $data[0],
                    'name'             => $name,
                    'free_stock'       => (int)$data[2],
                    'brand'            => $data[4],
                    'price'            => (float)$data[6],
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
                    // Используем line для построчного вывода с цветовой меткой
                    $this->line("<fg=cyan>[UPDATE]</> {$article} — {$name}");
                    $updated++;
                } else {
                    Product::create(array_merge(['article' => $article], $payload));
                    $this->line("<fg=green>[CREATE]</> {$article} — {$name}");
                    $created++;
                }
            }

            $this->info("\nИмпорт завершен:");
            $this->line("<fg=green>Создано новых: {$created}</>");
            $this->line("<fg=cyan>Обновлено: {$updated}</>");

            // --- ПРОВЕРКА ПРОПАВШИХ ТОВАРОВ ---
            $this->info("\nПроверка товаров, пропавших из прайса...");
            
            // Получаем список тех, кого будем скрывать, чтобы вывести их построчно
            $toDeactivate = Product::where('is_active', true)
                ->whereNotIn('article', $processedArticles)
                ->get();

            if ($toDeactivate->count() > 0) {
                foreach ($toDeactivate as $item) {
                    $item->update(['is_active' => false]);
                    $this->line("<fg=yellow>[HIDE]</> {$item->article} — {$item->name}");
                }
                $this->warn("\nСкрыто товаров: " . $toDeactivate->count());
            } else {
                $this->info("Все товары в базе актуальны.");
            }
            
        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
        }
    }
}