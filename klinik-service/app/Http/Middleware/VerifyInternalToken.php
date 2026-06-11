<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInternalToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('internal.api_token');

        if (! is_string($expectedToken) || $expectedToken === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal API token belum dikonfigurasi.',
            ], 500);
        }

        $providedToken = (string) $request->header('X-Internal-Token', '');

        if (! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized internal request.',
            ], 401);
        }

        return $next($request);
    }
}
