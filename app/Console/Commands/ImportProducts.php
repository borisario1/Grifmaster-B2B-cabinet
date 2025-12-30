<?php

/**
 * Название: ImportProducts.php
 * Дата-время: 28-12-2025 18:00
 * Описание: Консольная команда для синхронизации товаров из CSV (1С).
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class ImportProducts extends Command
{
    // Команда запуска: php artisan products:import
    protected $signature = 'products:import';
    protected $description = 'Импорт товаров из внешнего CSV файла (1С)';

    public function handle()
    {
        $this->info('Начало импорта...');
        
        $csvUrl = "http://data.grifmaster.ru/files/dq9/data/products.csv";
        
        try {
            $content = file_get_contents($csvUrl);
            if (!$content) throw new \Exception("Не удалось скачать файл.");

            $rows = explode("\n", $content);
            $count = 0;

            foreach ($rows as $index => $row) {
                if ($index === 0 || empty($row)) continue; // Пропуск заголовка и пустых строк

                $data = str_getcsv($row, ",");
                
                // Маппинг
                // 0:code_1c, 1:name, 2:free_stock, 3:article, 4:brand... 14:image_filename
                Product::updateOrCreate(
                    ['article' => $data[3]], 
                    [
                        'code_1c'          => $data[0],
                        'name'             => $data[1],
                        'free_stock'       => (int)$data[2],
                        'brand'            => $data[4],
                        'price'            => (float)$data[6],
                        'status'           => $data[10] ?? null,
                        'product_type'     => $data[11] ?? null,
                        'product_category' => $data[12] ?? null,
                        'collection'       => $data[13] ?? null,
                        'image_filename'   => $data[14] ?? null,
                    ]
                );
                $count++;
            }

            $this->info("Успешно импортировано/обновлено товаров: $count");
            
        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
        }
    }
}