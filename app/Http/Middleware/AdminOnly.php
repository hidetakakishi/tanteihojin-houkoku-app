<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !$user->is_admin) {
            abort(403, 'このページにアクセスする権限がありません。');
        }
        return $next($request);
    }
}