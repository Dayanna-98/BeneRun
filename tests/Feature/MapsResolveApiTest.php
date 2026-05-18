<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MapsResolveApiTest extends TestCase
{
    public function test_resolve_validates_required_url(): void
    {
        $this->postJson('/api/maps/resolve', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_resolve_returns_coordinates_when_url_contains_them(): void
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);

        $url = 'https://www.google.com/maps/@46.2044,6.1432,17z';

        $this->postJson('/api/maps/resolve', [
            'url' => $url,
        ])
            ->assertStatus(200)
            ->assertJsonPath('resolved_url', $url)
            ->assertJsonPath('latitude', 46.2044)
            ->assertJsonPath('longitude', 6.1432)
            ->assertJsonPath('has_coordinates', true);
    }

    public function test_resolve_handles_google_consent_continue_url(): void
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);

        $continue = rawurlencode('https://www.google.com/maps?q=46.1111,6.2222');
        $consentUrl = "https://consent.google.com/m?continue={$continue}";

        $this->postJson('/api/maps/resolve', [
            'url' => $consentUrl,
        ])
            ->assertStatus(200)
            ->assertJsonPath('resolved_url', 'https://www.google.com/maps?q=46.1111,6.2222')
            ->assertJsonPath('latitude', 46.1111)
            ->assertJsonPath('longitude', 6.2222)
            ->assertJsonPath('has_coordinates', true);
    }

    public function test_resolve_returns_422_when_upstream_fails(): void
    {
        Http::fake([
            '*' => Http::response('error', 500),
        ]);

        $this->postJson('/api/maps/resolve', [
            'url' => 'https://maps.app.goo.gl/test',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Failed to resolve URL')
            ->assertJsonPath('message', 'The shortened URL could not be resolved');
    }

    public function test_resolve_returns_no_coordinates_when_none_are_found(): void
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);

        $url = 'https://www.google.com/maps/place/SomePlaceWithoutCoords';

        $this->postJson('/api/maps/resolve', [
            'url' => $url,
        ])
            ->assertStatus(200)
            ->assertJsonPath('resolved_url', $url)
            ->assertJsonPath('latitude', null)
            ->assertJsonPath('longitude', null)
            ->assertJsonPath('has_coordinates', false);
    }
}
