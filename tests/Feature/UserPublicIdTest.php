<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPublicIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_get_a_public_id(): void
    {
        $user = User::factory()->create();

        $this->assertNotEmpty($user->public_id);
        $this->assertSame(26, strlen($user->public_id));
    }

    public function test_public_id_is_used_as_route_key(): void
    {
        $user = User::factory()->create();

        $this->assertSame('public_id', $user->getRouteKeyName());
        $this->assertSame($user->public_id, $user->getRouteKey());
    }

    public function test_public_id_is_unique(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->assertNotSame($first->public_id, $second->public_id);
    }
}
