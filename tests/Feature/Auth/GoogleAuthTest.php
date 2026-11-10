<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(array $overrides = []): void
    {
        $raw = array_merge([
            'sub' => 'google-123',
            'email' => 'applicant@example.com',
            'email_verified' => true,
            'name' => 'Applicant Name',
            'picture' => 'https://example.com/avatar.jpg',
        ], $overrides);

        $user = (new SocialiteUser)->setRaw($raw)->map([
            'id' => $raw['sub'],
            'nickname' => null,
            'name' => $raw['name'],
            'email' => $raw['email'],
            'avatar' => $raw['picture'],
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($user);
        $provider->shouldReceive('scopes')->andReturnSelf();
        $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_google_redirect_sends_guest_to_google(): void
    {
        $this->fakeGoogleUser();

        $this->get('/auth/google/redirect')->assertRedirect('https://accounts.google.com');
    }

    public function test_new_google_user_creates_verified_account_and_logs_in(): void
    {
        $this->fakeGoogleUser();

        $this->get('/auth/google/redirect?privacy=1');
        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $user = User::where('email', 'applicant@example.com')->firstOrFail();
        $this->assertSame('google-123', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->privacy_policy_accepted_at);
        $this->assertNull($user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_new_google_user_without_privacy_acceptance_is_rejected(): void
    {
        $this->fakeGoogleUser();

        $this->get('/auth/google/callback')
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('google');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_returning_google_user_logs_in_by_google_id(): void
    {
        $user = User::factory()->create(['email' => 'applicant@example.com', 'google_id' => 'google-123']);
        $this->fakeGoogleUser();

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
    }

    public function test_google_login_links_to_verified_email_account_and_keeps_password(): void
    {
        $user = User::factory()->create(['email' => 'applicant@example.com', 'password' => 'secret-pass']);
        $this->fakeGoogleUser();

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertSame('google-123', $user->google_id);
        $this->assertNotNull($user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_on_unverified_email_account_clears_password_and_sends_reset(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['email' => 'applicant@example.com', 'password' => 'attacker-pass']);
        $this->fakeGoogleUser();

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->fakeGoogleUser(['email_verified' => false]);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_email_registration_requires_privacy_policy_acceptance(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('privacy_policy');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_registration_records_privacy_policy_acceptance(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'privacy_policy' => true,
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertNotNull($user->privacy_policy_accepted_at);
        $this->assertSame(config('talent.privacy_policy_version'), $user->privacy_policy_version);
    }

    public function test_authenticated_users_cannot_use_google_routes(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/auth/google/redirect')->assertRedirect(route('dashboard'));
    }

    public function test_google_only_account_cannot_log_in_with_password(): void
    {
        User::factory()->create(['email' => 'applicant@example.com', 'password' => null, 'google_id' => 'google-123']);

        $this->post('/login', ['email' => 'applicant@example.com', 'password' => 'anything'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_unverified_user_is_sent_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    public function test_email_registration_cannot_duplicate_a_google_account(): void
    {
        User::factory()->create(['email' => 'applicant@example.com', 'password' => null, 'google_id' => 'google-123']);

        $this->post('/register', [
            'name' => 'Applicant',
            'email' => 'Applicant@Example.com',
            'password' => 'password-123-abc',
            'password_confirmation' => 'password-123-abc',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::count());
    }
}
