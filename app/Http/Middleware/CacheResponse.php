<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $minutes = 30): Response
    {
        $response = $next($request);

        if ($request->isMethod('get') && $response->getStatusCode() === 200) {
            $seconds = $minutes * 60;
            
            // Генерируем уникальный ключ для этой версии страницы
            $etag = md5($response->getContent());
            
            $response->headers->set('Cache-Control', "public, max-age={$seconds}, immutable");
            $response->headers->set('ETag', $etag);
            
            // Удаляем заголовки, которые могут мешать кешированию
            $response->headers->remove('Pragma');
            $response->headers->remove('Expires');
        }

        return $response;
    }
}