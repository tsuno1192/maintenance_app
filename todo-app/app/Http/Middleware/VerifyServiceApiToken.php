<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyServiceApiToken
{
    /**
     * サービス間 API を Bearer トークンのみで認証する。
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.api_token');

        if ($expected === '' || $this->isInsecurePlaceholder($expected)) {
            return response()->json([
                'status' => 'error',
                'message' => 'API token is not securely configured on todo-app.',
            ], 503);
        }

        $provided = $request->bearerToken();

        if (! is_string($provided) || $provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }

    private function isInsecurePlaceholder(string $token): bool
    {
        if (! app()->environment('production')) {
            return false;
        }

        $blocked = [
            'change-me-to-a-long-random-string',
            'test-token',
            'secret',
            'password',
        ];

        return in_array($token, $blocked, true) || strlen($token) < 32;
    }
}
