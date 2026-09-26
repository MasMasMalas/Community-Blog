<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Feature tests for task 3.3 — Login & Logout
 *
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'username' => 'testuser',
            'password' => Hash::make('Password1'),
            'role' => 'author',
            'is_active' => true,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Req 2.1 — Login form is accessible to guests
    // -------------------------------------------------------------------------

    public function test_login_form_is_shown_to_guests(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Masuk');
        $response->assertSee('email');
        $response->assertSee('password');
    }

    // -------------------------------------------------------------------------
    // Req 2.2 — Valid credentials create a session
    // -------------------------------------------------------------------------

    public function test_valid_credentials_authenticate_user(): void
    {
        $user = $this->makeUser();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    // -------------------------------------------------------------------------
    // Req 2.3 — Invalid credentials return generic error (no user enumeration)
    // -------------------------------------------------------------------------

    public function test_wrong_password_returns_generic_error(): void
    {
        $user = $this->makeUser();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_nonexistent_email_returns_generic_error(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'nobody@example.com',
            'password' => 'Password1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // Req 2.4 — Rate limiting: 6th attempt is rejected with a throttle error
    // -------------------------------------------------------------------------

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        // Clear any leftover rate-limit state from other tests.
        RateLimiter::clear('nobody@rate-test.com|127.0.0.1');

        $payload = [
            'email' => 'nobody@rate-test.com',
            'password' => 'WrongPassword',
        ];

        // 5 failed attempts — all should return a validation error (not throttle).
        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('login'), $payload);
        }

        // 6th attempt must be throttled.
        $response = $this->post(route('login'), $payload);
        $response->assertSessionHasErrors('email');

        // The error message must mention retry time, not just "invalid credentials".
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', implode('', $errors));
    }

    // -------------------------------------------------------------------------
    // Inactive account — must be rejected even with correct password
    // -------------------------------------------------------------------------

    public function test_inactive_account_cannot_log_in(): void
    {
        $user = $this->makeUser(['is_active' => false]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // Req 2.5 — Logout invalidates the session
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_log_out(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // Req 2.6 — After logout, session can no longer access protected routes
    // -------------------------------------------------------------------------

    public function test_after_logout_session_is_no_longer_valid(): void
    {
        $user = $this->makeUser();

        // Authenticate, then log out.
        $this->actingAs($user)->post(route('logout'));

        // The session is now invalid — any auth-protected route should redirect.
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Req 2.1 — Authenticated user is redirected away from the login form
    // -------------------------------------------------------------------------

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get(route('login'));

        // Guest middleware should redirect authenticated users away.
        $response->assertRedirect();
    }
}
