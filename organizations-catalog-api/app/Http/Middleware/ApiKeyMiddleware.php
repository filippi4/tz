<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->query('key');
        $validApiKey = config('api.key');

        if (! $apiKey || $apiKey !== $validApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
                'error_code' => 'INVALID_API_KEY',
            ], 401);
        }

        return $next($request);
    }
}
