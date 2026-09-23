<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIoTSensorToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.iot_sensor_token');

        if ($expected === '' || strlen($expected) < 16) {
            return response()->json([
                'status' => 'error',
                'message' => 'IoT sensor token is not configured.',
            ], 503);
        }

        $provided = $request->bearerToken();
        if (! is_string($provided) || ! hash_equals($expected, $provided)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
