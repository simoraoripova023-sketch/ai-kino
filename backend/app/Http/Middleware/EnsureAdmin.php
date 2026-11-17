<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json([
                'error' => [
                    'code' => 'ADMIN_ACCESS_REQUIRED',
                    'message' => 'Ushbu amalni bajarish uchun administrator huquqi talab qilinadi.',
                ],
            ], 403);
        }

        return $next($request);
    }
}
