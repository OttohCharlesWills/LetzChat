<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeolocationService
{
    /**
     * Resolve an IP address to country/state/city/timezone. Returns nulls
     * for everything if the IP is private/local (dev environments) or the
     * lookup fails — callers should treat a null result as "unknown," not
     * as an error to bubble up.
     */
    public function lookup(string $ip): array
    {
        $empty = ['country' => null, 'state' => null, 'city' => null, 'timezone' => null];

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $empty; // localhost, private LAN ranges, etc. — nothing to look up
        }

        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,country,regionName,city,timezone',
            ]);

            if (! $response->successful() || $response->json('status') !== 'success') {
                return $empty;
            }

            return [
                'country'  => $response->json('country'),
                'state'    => $response->json('regionName'),
                'city'     => $response->json('city'),
                'timezone' => $response->json('timezone'),
            ];
        } catch (\Throwable $e) {
            Log::warning('Geolocation lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);
            return $empty;
        }
    }
}