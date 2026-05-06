<?php

namespace Tests\Unit;

use App\Support\GoogleMapsUrl;
use PHPUnit\Framework\TestCase;

class GoogleMapsUrlTest extends TestCase
{
    public function test_it_extracts_coordinates_from_common_google_maps_urls(): void
    {
        $coordinates = GoogleMapsUrl::extractCoordinates('https://www.google.com/maps/place/Test/@46.2044,6.1432,17z');

        $this->assertSame(46.2044, $coordinates['latitude']);
        $this->assertSame(6.1432, $coordinates['longitude']);
    }

    public function test_it_returns_null_when_no_coordinates_are_present(): void
    {
        $this->assertNull(GoogleMapsUrl::extractCoordinates('https://www.google.com/maps/place/Geneve'));
    }
}