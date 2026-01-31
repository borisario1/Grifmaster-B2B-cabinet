<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecureDownloadController extends Controller
{
    /**
     * Handle secure file download with optional confirmation.
     */
    public function download(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);
        
        // Проверка активности
        if (!$resource->is_active) {
            abort(404, 'Файл временно недоступен');
        }
        
        // Проверка подтверждения (для всех типов: и файлов, и внешних ссылок)
        if ($resource->require_confirmation && !$request->has('confirmed')) {
            // Если это AJAX запрос, вернуть JSON с данными для модалки
            return response()->json([
                'require_confirmation' => true,
                'confirmation_text' => $resource->confirmation_text,
                'confirm_btn_text' => $resource->confirm_btn_text,
                'title' => $resource->title,
            ]);
        }
        
        // Логирование
        $this->logDownload($resource, $request);
        
        // Если это внешняя ссылка
        if ($resource->external_link) {
            return redirect($resource->external_link);
        }
        
        // Проверка существования файла
        if (!$resource->file_path || !Storage::disk('local')->exists($resource->file_path)) {
            abort(404, 'Файл не найден на сервере. Пожалуйста, обратитесь к администратору.');
        }
        
        // Отдача файла
        $fileName = $this->generateFileName($resource);
        
        return Storage::disk('local')->download(
            $resource->file_path,
            $fileName
        );
    }
    
    /**
     * Show confirmation page with token generation.
     */
    private function showConfirmation($resource)
    {
        $token = $this->generateToken($resource->id);
        
        session()->put("download_token_{$token}", [
            'resource_id' => $resource->id,
            'expires_at' => now()->addMinutes(5)
        ]);
        
        return view('files.confirm', compact('resource', 'token'));
    }
    
    /**
     * Validate the one-time download token.
     */
    private function validateToken($request)
    {
        $token = $request->input('token');
        $data = session()->get("download_token_{$token}");
        
        if (!$data || now()->gt($data['expires_at'])) {
            abort(403, 'Токен недействителен или истек. Пожалуйста, вернитесь и ознакомьтесь с условиями снова.');
        }
        
        // Удаляем использованный токен
        session()->forget("download_token_{$token}");
    }
    
    /**
     * Generate a unique one-time token.
     */
    private function generateToken($resourceId)
    {
        return md5($resourceId . '|' . now()->timestamp . '|' . Str::random(16));
    }
    
    /**
     * Log the download action.
     */
    private function logDownload($resource, $request)
    {
        ResourceStat::create([
            'resource_id' => $resource->id,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'downloaded_at' => now(),
        ]);
    }
    
    /**
     * Generate a unique filename for download.
     */
    private function generateFileName($resource)
    {
        $baseName = Str::slug($resource->title);
        $extension = $resource->getFileExtension();
        $dateTag = now()->format('Ymd_His');
        
        return "{$baseName}_{$dateTag}.{$extension}";
    }
}
