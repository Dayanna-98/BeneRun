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

        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $value, $matches)) {
                continue;
            }

            $latitude = (float) $matches[1];
            $longitude = (float) $matches[2];

            if (!self::isValidCoordinatePair($latitude, $longitude)) {
                continue;
            }

            return [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        }

        return null;
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