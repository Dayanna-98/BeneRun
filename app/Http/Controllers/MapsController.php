<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapsController extends Controller
{
    /**
     * Resolve a shortened Google Maps URL to get the full URL with coordinates
     *
     * @return JsonResponse
     */
    public function resolve(Request $request)
    {
        $request->validate([
            'url' => 'required|string|max:1000',
        ]);

        try {
            $shortenedUrl = trim((string) $request->input('url'));

            // Follow redirects to get the full URL when possible.
            $response = Http::withOptions([
                'allow_redirects' => true,
            ])
                ->timeout(10)
                ->get($shortenedUrl);

            if ($response->status() >= 400) {
                return response()->json([
                    'error' => 'Failed to resolve URL',
                    'message' => 'The shortened URL could not be resolved',
                ], 422);
            }

            // Some environments/middlewares may not expose an effective URL.
            $resolvedUrl = method_exists($response, 'effectiveUri')
                ? (string) $response->effectiveUri()
                : $shortenedUrl;

            if ($resolvedUrl === '') {
                $resolvedUrl = $shortenedUrl;
            }

            $resolvedUrl = $this->normalizeResolvedUrl($resolvedUrl);

            // Extract coordinates from the resolved URL
            $coordinates = $this->extractCoordinates($resolvedUrl);

            return response()->json([
                'resolved_url' => $resolvedUrl,
                'latitude' => $coordinates['latitude'] ?? null,
                'longitude' => $coordinates['longitude'] ?? null,
                'has_coordinates' => (bool) $coordinates,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to resolve URL',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Extract coordinates from a Google Maps URL
     *
     * @param  string  $url
     * @return array|null
     */
    private function extractCoordinates($url)
    {
        // Pattern 1: @latitude,longitude in the URL (most common)
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2],
            ];
        }

        // Pattern 2: query parameter format
        if (preg_match('/[?&]@?=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2],
            ];
        }

        // Pattern 3: place_id or other location identifiers
        // Try to parse as parsed coordinates in different formats
        if (preg_match('/(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2],
            ];
        }

        return null;
    }

    /**
     * Unwrap Google consent redirect URLs to the underlying maps URL.
     *
     * @param  string  $url
     * @return string
     */
    private function normalizeResolvedUrl($url)
    {
        $parsed = parse_url($url);
        if (! $parsed || empty($parsed['host'])) {
            return $url;
        }

        if (str_contains($parsed['host'], 'consent.google.com') && ! empty($parsed['query'])) {
            parse_str($parsed['query'], $query);
            if (! empty($query['continue'])) {
                return urldecode((string) $query['continue']);
            }
        }

        return $url;
    }
}
