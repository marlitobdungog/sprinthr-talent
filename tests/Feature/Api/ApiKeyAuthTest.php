<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiKeyAuthTest extends TestCase
{
    public function test_missing_bearer_token_is_rejected(): void
    {
        config(['talent.api_keys' => ['secret-key-123456789']]);

        $this->getJson('/api/v1/ping')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_wrong_key_is_rejected(): void
    {
        config(['talent.api_keys' => ['secret-key-123456789']]);

        $this->withHeaders(['Authorization' => 'Bearer wrong-key'])
            ->getJson('/api/v1/ping')
            ->assertUnauthorized();
    }

    public function test_empty_configured_key_fails_closed(): void
    {
        config(['talent.api_keys' => []]);

        $this->withHeaders(['Authorization' => 'Bearer '])
            ->getJson('/api/v1/ping')
            ->assertUnauthorized();

        $this->withHeaders(['Authorization' => 'Bearer anything'])
            ->getJson('/api/v1/ping')
            ->assertUnauthorized();
    }

    public function test_valid_key_reaches_the_endpoint_as_a_service_principal(): void
    {
        config(['talent.api_keys' => ['secret-key-123456789']]);

        $this->withHeaders(['Authorization' => 'Bearer secret-key-123456789'])
            ->getJson('/api/v1/ping')
            ->assertOk()
            ->assertJson(['data' => ['status' => 'ok', 'principal' => 'service']]);
    }

    public function test_any_of_several_keys_is_accepted_for_rotation(): void
    {
        config(['talent.api_keys' => ['old-key-aaaaaaaaaaaa', 'new-key-bbbbbbbbbbbb']]);

        $this->withHeaders(['Authorization' => 'Bearer old-key-aaaaaaaaaaaa'])->getJson('/api/v1/ping')->assertOk();
        $this->withHeaders(['Authorization' => 'Bearer new-key-bbbbbbbbbbbb'])->getJson('/api/v1/ping')->assertOk();
    }

    public function test_response_never_echoes_the_key(): void
    {
        config(['talent.api_keys' => ['secret-key-123456789']]);

        $response = $this->withHeaders(['Authorization' => 'Bearer secret-key-123456789'])
            ->getJson('/api/v1/ping');

        $this->assertStringNotContainsString('secret-key-123456789', $response->getContent());
    }
}
