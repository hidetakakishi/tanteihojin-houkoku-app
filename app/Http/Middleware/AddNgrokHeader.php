<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddNgrokHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ngrokの警告回避ヘッダーを追加
        $response->headers->set('ngrok-skip-browser-warning', 'true');

        return $response;
    }
}