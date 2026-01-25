<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ProductController extends Controller
{
    /**
     * Универсальный метод переключения (Лайк или Вишлист)
     */
    /**
     * Универсальный метод переключения (Лайк или Вишлист)
     */
    private function toggleInteraction($id, $type, $counterField)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);
        
        // 1. Проверяем наличие записи
        $existing = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->where('product_id', $id)
            ->where('type', $type)
            ->first();

        $isActive = false;

        if ($existing) {
            // УДАЛЯЕМ
            DB::table('b2b_product_interactions')->where('id', $existing->id)->delete();
            $isActive = false;
        } else {
            // ДОБАВЛЯЕМ
            DB::table('b2b_product_interactions')->insert([
                'user_id' => $user->id,
                'product_id' => $id,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $isActive = true;
        }

        // 2. ПЕРЕСЧИТЫВАЕМ И ОБНОВЛЯЕМ СЧЕТЧИК (САМОЕ ВАЖНОЕ!)
        $realCount = DB::table('b2b_product_interactions')
            ->where('product_id', $id)
            ->where('type', $type)
            ->count();

        $details = $product->details()->firstOrCreate([]);
        $details->$counterField = $realCount;
        $details->save();

        return response()->json([
            'success' => true,
            'active'  => $isActive,
            'count'   => $realCount // Возвращаем реальное число для JS
        ]);
    }

    public function toggleLike($id)
    {
        return $this->toggleInteraction($id, 'like', 'likes_count');
    }

    public function toggleWishlist($id)
    {
        return $this->toggleInteraction($id, 'wishlist', 'wishlist_count');
    }

    /**
     * Скачивание изображений (без изменений, оставляем как было)
     */
    public function downloadImages($id)
    {
        $product = Product::with('details')->findOrFail($id);
        $details = $product->details;
        
        if (!$details || empty($details->images)) {
            return back()->with('error', 'Изображения отсутствуют');
        }

        $images = json_decode($details->images, true);
        if (empty($images)) {
            return back()->with('error', 'Изображения отсутствуют');
        }

        $config = config('b2b_store.quick_view');
        $zipFileName = 'images_' . Str::slug($product->article) . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($images as $index => $img) {
                $url = $img['url_big'] ?? null;
                if (!$url) continue;

                if (!Str::startsWith($url, ['http', 'https'])) {
                    $domain = rtrim($config['media_url'] ?? 'https://grifmaster.ru', '/');
                    $url = $domain . '/' . ltrim($url, '/');
                }
                $url = str_replace('.970.', '.2000.', $url);

                try {
                    $response = Http::timeout(10)->get($url);
                    if ($response->successful()) {
                        $extension = pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg';
                        $zip->addFromString($product->article . '_' . ($index + 1) . '.' . $extension, $response->body());
                    }
                } catch (\Exception $e) { continue; }
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Данные для модального окна (Обновлено)
     */
    public function quickView($id)
    {
        $user = Auth::user();
        $product = Product::with('details')->findOrFail($id);
        $details = $product->details;
        $config  = config('b2b_store.quick_view');
        $mediaUrl = rtrim($config['media_url'] ?? 'https://grifmaster.ru', '/');

        // Проверяем состояние для текущего юзера
        $isLiked = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->where('product_id', $id)
            ->where('type', 'like')
            ->exists();

        $isInWishlist = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->where('product_id', $id)
            ->where('type', 'wishlist')
            ->exists();

        // --- ЛОГИКА ИЗОБРАЖЕНИЙ ---
        $processImageUrl = function($url) use ($config, $mediaUrl) {
            if (empty($url)) return null;
            if (!Str::startsWith($url, ['http', 'https'])) {
                $url = $mediaUrl . '/' . ltrim($url, '/');
            }
            $quality = $config['image_quality'] ?? '970';
            if ($quality !== '970') {
                return str_replace('.970.', '.' . $quality . '.', $url);
            }
            return $url;
        };

        $mainImage = $product->image_url;
        $gallery = [];

        if (($config['image_priority'] ?? '1c') === 'webasyst' && $details && !empty($details->images)) {
            $waImages = json_decode($details->images, true);
            if (!empty($waImages) && is_array($waImages)) {
                foreach ($waImages as $img) {
                    if (isset($img['url_big'])) {
                        $gallery[] = $processImageUrl($img['url_big']);
                    }
                }
                if (!empty($gallery)) {
                    $mainImage = $gallery[0];
                }
            }
        }
        
        if (empty($gallery) && $mainImage) {
            $gallery[] = $mainImage;
        }

        // --- ХАРАКТЕРИСТИКИ ---
        $rawFeatures = ($details && !empty($details->features)) ? json_decode($details->features, true) : [];
        $featuresMain = [];
        $featuresLogistics = [];
        $logisticsCodes = [
            'weight', 'length', 'shirina', 'vysota_sm', 
            'obem_m3', 'material_upakovki', 'shtrikhkod', 
            'nomenklaturnyy_nomer_1s', '1c_status_gusev'
        ];
        $allowed = $config['allowed_features'] ?? [];
        $labels  = $config['feature_labels'] ?? [];

        if (is_array($rawFeatures)) {
            foreach ($rawFeatures as $feat) {
                $code = $feat['code'] ?? null;
                if (!empty($allowed) && !in_array($code, $allowed)) continue;
                $name = $labels[$code] ?? $feat['name'] ?? $feat['feature_name'] ?? 'Хар-ка';
                $val  = $feat['value'] ?? $feat['text_value'] ?? $feat['value_name'] ?? '-';
                if (is_string($val) && str_contains($val, 'src="/')) {
                    $val = str_replace('src="/', 'src="' . $mediaUrl . '/', $val);
                }
                $item = ['name' => $name, 'value' => $val];
                if (in_array($code, $logisticsCodes)) $featuresLogistics[] = $item;
                else $featuresMain[] = $item;
            }
        }

        // --- ДОКУМЕНТЫ ---
        $docs = [];
        if ($details && !empty($details->documents)) {
            $rawDocs = json_decode($details->documents, true);
            foreach ($rawDocs as $doc) {
                $url = $doc['url'] ?? '#';
                if (!Str::startsWith($url, ['http', 'https'])) {
                    $url = $mediaUrl . '/' . ltrim($url, '/');
                }
                $docs[] = [
                    'name' => $doc['name'] ?? 'Документ',
                    'ext'  => $doc['ext'] ?? pathinfo($url, PATHINFO_EXTENSION),
                    'url'  => $url
                ];
            }
        }

        // --- ССЫЛКА НА САЙТ ---
        $productUrl = null;
        if ($details && $details->url_slug) {
            $cleanSlug = ltrim($details->url_slug, '/');
            $productUrl = $mediaUrl . '/' . $cleanSlug;
        }

        if ($details) {
            $details->increment('views_count');
            $details->update(['last_viewed_at' => now()]);
        }

        return response()->json([
            'success'     => true,
            'name'        => $product->name,
            'article'     => $product->article,
            'price'       => number_format($product->price, 2, ',', ' ') . ' ₽',
            'image'       => $mainImage, 
            'gallery'     => array_values(array_unique($gallery)),
            'summary'     => ($config['show_summary'] ?? true) ? ($details->summary ?? '') : '',
            'description' => $details->description ?? '',
            'rating'      => ($config['show_rating'] ?? true) ? ($details->rating ?? 0) : 0,
            'rating_count'=> $details->rating_count ?? 0,
            
            // Новые поля для статуса
            'is_liked'       => $isLiked,
            'is_in_wishlist' => $isInWishlist,
            
            'features'    => $featuresMain,
            'logistics'   => $featuresLogistics,
            'stock_status'=> $product->free_stock > 0 ? 'В наличии' : 'Нет в наличии',
            'stock_qty'   => $product->free_stock,
            'documents'   => $docs,
            'product_url' => $productUrl,
            'download_url'=> route('catalog.download_images', $id)
        ]);
    }
}