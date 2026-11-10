<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleController extends Controller
{
    /**
     * Redirect the user to Google's consent screen.
     */
    public function redirect(Request $request): RedirectResponse
    {
        if ($request->boolean('privacy')) {
            $request->session()->put('google_privacy_accepted', true);
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Sign in or create the account for the Google user returning from Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        $privacyAccepted = $request->session()->pull('google_privacy_accepted', false);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return to_route('login')->withErrors(['google' => 'Google sign-in expired. Please try again.']);
        } catch (\Throwable $e) {
            Log::warning('Google sign-in failed', ['message' => $e->getMessage()]);

            return to_route('login')->withErrors(['google' => 'Google sign-in failed. Please try again.']);
        }

        $email = Str::lower((string) $googleUser->getEmail());
        $googleId = (string) $googleUser->getId();

        if (! $email || ! $googleId || ($googleUser->user['email_verified'] ?? false) !== true) {
            return to_route('login')->withErrors(['google' => 'Your Google account email must be verified to sign in.']);
        }

        $user = User::where('google_id', $googleId)->first();

        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $this->linkToExistingAccount($user, $googleId, $googleUser->getAvatar());
            } else {
                if (! $privacyAccepted) {
                    return to_route('register')->withErrors(['google' => 'Please accept the Privacy Policy to create an account with Google.']);
                }

                $user = User::forceCreate([
                    'name' => $googleUser->getName() ?: $email,
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'privacy_policy_accepted_at' => now(),
                    'privacy_policy_version' => config('talent.privacy_policy_version'),
                ]);

                event(new Registered($user));
            }
        }

        Auth::login($user, remember: true);

        return to_route('dashboard');
    }

    /**
     * Link a Google identity to an existing account with the same email.
     *
     * If the account's email was never verified, it may have been registered
     * by someone else before the real owner. Google has now proven ownership,
     * so the password is cleared and the owner must set a new one.
     */
    private function linkToExistingAccount(User $user, string $googleId, ?string $avatar): void
    {
        $wasUnverified = $user->email_verified_at === null;

        $user->forceFill([
            'google_id' => $googleId,
            'avatar' => $avatar,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'password' => $wasUnverified ? null : $user->password,
        ])->save();

        if ($wasUnverified) {
            $user->sendPasswordResetNotification(
                Password::broker()->createToken($user),
            );
        }
    }
}
