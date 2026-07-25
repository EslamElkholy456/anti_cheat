<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AiAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-AI-Key');

        if (!$key || $key !== config('services.ai.secret_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid AI service key.',
            ], 401);
        }

        return $next($request);
    }
}
