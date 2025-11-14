<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $clientId = $request->header('x-client-id');
        $clientKey = $request->header('x-client-key');

        if (! $clientId || ! $clientKey) {
            return response()->json([
                'message' => 'Missing client headers.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $site = Site::query()
            ->whereKey($clientId)
            ->where('key', $clientKey)
            ->where('status', true)
            ->first();

        if (! $site) {
            return response()->json([
                'message' => 'Invalid client credentials.',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->attributes->set('site', $site);

        return $next($request);
    }
}
