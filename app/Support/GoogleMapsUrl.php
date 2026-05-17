<?php

namespace App\Support;

final class GoogleMapsUrl
{
    public static function extractCoordinates(?string $url): ?array
    {
        $value = trim((string) $url);

        if ($value === '') {
            return null;
        }

        $patterns = [
            '/[?&](?:q|query)=(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)/i',
            '/@(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)(?:,|$)/',
            '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/',
            '/(?:^|\s)(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)(?:\s|$)/',
        ];

        $candidateUrls = [$value];

        if (self::isPotentialShortGoogleMapsUrl($value)) {
            $resolved = self::resolveFinalUrl($value);
            if ($resolved && ! in_array($resolved, $candidateUrls, true)) {
                $candidateUrls[] = $resolved;
            }
        }

        foreach ($candidateUrls as $candidateUrl) {
            foreach ($patterns as $pattern) {
                if (! preg_match($pattern, $candidateUrl, $matches)) {
                    continue;
                }

                $latitude = (float) $matches[1];
                $longitude = (float) $matches[2];

                if (! self::isValidCoordinatePair($latitude, $longitude)) {
                    continue;
                }

                return [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            }
        }

        return null;
    }

    private static function isPotentialShortGoogleMapsUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, [
            'maps.app.goo.gl',
            'goo.gl',
            'g.co',
        ], true);
    }

    private static function resolveFinalUrl(string $url): ?string
    {
        if (! function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_USERAGENT => 'BeneRun-MapsResolver/1.0',
        ]);

        curl_exec($ch);

        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        if (! is_string($effectiveUrl) || trim($effectiveUrl) === '') {
            return null;
        }

        return $effectiveUrl;
    }

    public static function isValidCoordinatePair(float $latitude, float $longitude): bool
    {
        return $latitude >= -90
            && $latitude <= 90
            && $longitude >= -180
            && $longitude <= 180;
    }

    public static function distanceInMeters(
        float $originLatitude,
        float $originLongitude,
        float $targetLatitude,
        float $targetLongitude
    ): float {
        $earthRadius = 6371000;

        $deltaLatitude = deg2rad($targetLatitude - $originLatitude);
        $deltaLongitude = deg2rad($targetLongitude - $originLongitude);

        $a = sin($deltaLatitude / 2) ** 2
            + cos(deg2rad($originLatitude))
            * cos(deg2rad($targetLatitude))
            * sin($deltaLongitude / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
