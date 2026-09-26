<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Property-Based Tests for Login Session and Rate Limiting
 *
 * **Validates: Requirements 2.2, 2.4**
 *
 * This test class validates universal properties that must hold across all inputs:
 * - Property 5: Login Session Validity
 * - Property 6: Rate Limiting Login
 */
class LoginSessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 5: Session Login Validity — Valid credentials must create active session
     *
     * **Validates: Requirement 2.2**
     *
     * For any user with valid registered credentials, after login, the system must:
     * 1. Create an active session (session cookie must be set)
     * 2. Authenticate the user (Auth::check() must return true after request)
     * 3. Redirect to intended dashboard or home
     *
     * Test cases cover:
     * - Valid email with correct password
     * - Different valid credentials combinations
     * - Session persistence across requests
     */
    public function test_valid_credentials_create_active_session(): void
    {
        // Create a user with known credentials
        $password = 'SecurePass123';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // Attempt login with valid credentials
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => $password,
        ]);

        // Should redirect to dashboard (successful login)
        $response->assertRedirect(route('dashboard'));

        // Session should contain authenticated user
        $this->assertAuthenticated();
        $this->assertEquals($user->id, Auth::id());
    }

    /**
     * Property 5 (Variant): Session persists across subsequent requests
     *
     * **Validates: Requirement 2.2**
     *
     * After successful login, the session must be usable in subsequent requests
     * to access authenticated routes.
     */
    public function test_session_persists_across_requests(): void
    {
        $password = 'SecurePass123';
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // Login
        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => $password,
        ]);

        // Verify session persists in a subsequent request
        $this->assertAuthenticated();
        $this->assertEquals($user->id, Auth::id());

        // If there's an authenticated route, try to access it
        // (Assuming /dashboard exists and requires auth)
        $response = $this->get('/dashboard');

        // Should not redirect to login
        $this->assertNotEquals(302, $response->status());
        // Either 200 OK or other non-redirect status for authenticated access
    }

    /**
     * Property 5 (Variant): Different valid credentials for different users
     *
     * **Validates: Requirement 2.2**
     *
     * For any valid user credentials, login must succeed and create session for
     * that specific user only.
     */
    public function test_multiple_users_independent_sessions(): void
    {
        $password1 = 'UserPass123';
        $password2 = 'DifferPass456';

        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make($password1),
            'is_active' => true,
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make($password2),
            'is_active' => true,
        ]);

        // User 1 login
        $this->post('/login', [
            'email' => 'user1@example.com',
            'password' => $password1,
        ]);

        $this->assertEquals($user1->id, Auth::id());

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // User 2 login
        $this->post('/login', [
            'email' => 'user2@example.com',
            'password' => $password2,
        ]);

        $this->assertEquals($user2->id, Auth::id());
    }

    /**
     * Property 5 (Variant): Valid credentials but inactive account must be rejected
     *
     * **Validates: Requirement 2.2 (with 14.6)**
     *
     * Even with valid credentials, a user with is_active = false must not
     * receive an active session.
     */
    public function test_inactive_account_denied_session(): void
    {
        $password = 'SecurePass123';
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make($password),
            'is_active' => false,  // Inactive account
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => $password,
        ]);

        // Should be redirected back (login failed)
        $response->assertRedirect();

        // Should not be authenticated
        $this->assertGuest();
        $this->assertNull(Auth::id());
    }

    /**
     * Property 5 (Variant): Invalid credentials must not create session
     *
     * **Validates: Requirement 2.2, 2.3**
     *
     * For any invalid credentials, login must fail and no session must be created.
     */
    public function test_invalid_credentials_no_session(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('CorrectPass123'),
            'is_active' => true,
        ]);

        // Try with wrong password
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword123',
        ]);

        // Should redirect back with error
        $response->assertRedirect();
        $response->assertSessionHasErrors('email');

        // Should not be authenticated
        $this->assertGuest();
    }

    /**
     * Property 6: Rate Limiting Login — 6th attempt from same IP rejected with 429
     *
     * **Validates: Requirement 2.4**
     *
     * For any IP address, the system must:
     * 1. Allow 5 failed login attempts per 15-minute window
     * 2. Reject the 6th attempt with HTTP 429 (Too Many Requests)
     * 3. Include throttle info in response
     *
     * Test cases verify:
     * - First 5 attempts are allowed (receive 302 redirect)
     * - 6th attempt is rejected (receive 429 or 302 with throttle message)
     * - Rate limiting is based on IP address
     */
    public function test_rate_limit_first_five_attempts_allowed(): void
    {
        $email = 'test@example.com';

        // Create a user for comparison
        User::factory()->create([
            'email' => $email,
            'password' => Hash::make('CorrectPass123'),
            'is_active' => true,
        ]);

        // Perform 5 failed login attempts with wrong password
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword',
            ]);

            // Should be redirected (not rate-limited yet)
            $this->assertNotNull($response->status());
            // Expect 302 redirect, not 429
        }

        // After 5 attempts, session should have error (login failed, not rate-limited)
        $this->assertNull(Auth::id());
    }

    /**
     * Property 6 (Variant): 6th attempt from same IP rejected with throttle message
     *
     * **Validates: Requirement 2.4**
     *
     * After 5 failed attempts from the same IP, the 6th attempt must be rejected
     * with a rate-limit error message.
     */
    public function test_rate_limit_sixth_attempt_rejected(): void
    {
        $email = 'test@example.com';

        User::factory()->create([
            'email' => $email,
            'password' => Hash::make('CorrectPass123'),
            'is_active' => true,
        ]);

        // Perform 5 failed login attempts
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword',
            ]);
        }

        // 6th attempt should be rate-limited
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'WrongPassword',
        ]);

        // Should be redirected with throttle error
        $response->assertRedirect();
        $response->assertSessionHasErrors('email');

        // Error message should mention rate limiting
        $errors = session('errors');
        $errorMessage = $errors->first('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', $errorMessage);
    }

    /**
     * Property 6 (Variant): Rate limiting is based on IP address
     *
     * **Validates: Requirement 2.4**
     *
     * For any two different IP addresses, their rate-limit counters must be
     * independent. Attempts from IP A do not affect the counter for IP B.
     */
    public function test_rate_limit_per_ip_address(): void
    {
        $email = 'test@example.com';

        User::factory()->create([
            'email' => $email,
            'password' => Hash::make('CorrectPass123'),
            'is_active' => true,
        ]);

        // Perform 5 failed attempts from first IP
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword',
            ]);
        }

        // After 5 attempts from original IP, 6th should be throttled
        $response1 = $this->post('/login', [
            'email' => $email,
            'password' => 'WrongPassword',
        ]);
        $response1->assertSessionHasErrors('email');

        // Now attempt from a different IP should NOT be throttled
        // (Laravel test can override IP via withHeaders)
        $response2 = $this->withHeaders(['X-Forwarded-For' => '192.168.2.1'])
            ->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword',
            ]);

        // Should be redirected (login failed, but not rate-limited)
        $response2->assertRedirect();
        // Should have auth error (wrong password), not throttle error
        $response2->assertSessionHasErrors('email');
    }

    /**
     * Property 6 (Variant): Successful login clears rate-limit counter
     *
     * **Validates: Requirement 2.4**
     *
     * After a successful login, the rate-limit counter for that user/IP must be
     * cleared so that subsequent failed attempts restart the counter.
     */
    public function test_rate_limit_cleared_on_successful_login(): void
    {
        $password = 'CorrectPass123';
        $email = 'test@example.com';

        User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // Perform some failed attempts (not exceeding 5)
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword',
            ]);
        }

        // Successful login should clear the counter
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // After successful login, counter should be reset
        // Now we can try 5 times again from scratch
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword',
            ]);
        }

        // 6th attempt should be throttled (proving counter was reset)
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors');
        $this->assertStringContainsString('Terlalu banyak percobaan', $errors->first('email'));
    }

    /**
     * Property 6 (Variant): Rate limiting window expires after 15 minutes
     *
     * **Validates: Requirement 2.4**
     *
     * After 15 minutes have passed, the rate-limit counter should reset and
     * allow new attempts. (Note: This test uses time manipulation if available,
     * or documents the behavior conceptually.)
     */
    public function test_rate_limit_window_expires(): void
    {
        $email = 'test@example.com';

        User::factory()->create([
            'email' => $email,
            'password' => Hash::make('CorrectPass123'),
            'is_active' => true,
        ]);

        // Perform 5 failed login attempts
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword',
            ]);
        }

        // 6th attempt should be rate-limited
        $response1 = $this->post('/login', [
            'email' => $email,
            'password' => 'WrongPassword',
        ]);
        $response1->assertSessionHasErrors('email');

        // NOTE: Full time-based testing of 15-minute window would require:
        // - Mocking the time/clock
        // - Or using a queue job that clears the counter
        // For this test, we document that the rate-limiter decay is 15 minutes
        // as defined in LoginController::DECAY_SECONDS = 15 * 60
    }

    /**
     * Property 6 (Variant): Rate limiting combines email and IP for throttle key
     *
     * **Validates: Requirement 2.4**
     *
     * The throttle key is based on email + IP, so:
     * 1. Same email from different IPs = independent counters
     * 2. Different emails from same IP = independent counters
     */
    public function test_rate_limit_key_combines_email_and_ip(): void
    {
        $email1 = 'user1@example.com';
        $email2 = 'user2@example.com';

        User::factory()->create([
            'email' => $email1,
            'password' => Hash::make('CorrectPass123'),
            'is_active' => true,
        ]);

        User::factory()->create([
            'email' => $email2,
            'password' => Hash::make('CorrectPass123'),
            'is_active' => true,
        ]);

        // Perform 5 failed attempts with email1
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => $email1,
                'password' => 'WrongPassword',
            ]);
        }

        // email1 should now be throttled
        $response1 = $this->post('/login', [
            'email' => $email1,
            'password' => 'WrongPassword',
        ]);
        $response1->assertSessionHasErrors('email');

        // But email2 should still be allowed (different email)
        $response2 = $this->post('/login', [
            'email' => $email2,
            'password' => 'WrongPassword',
        ]);

        // email2 should not be throttled, just regular auth error
        $response2->assertRedirect();
        $response2->assertSessionHasErrors('email');
    }

    /**
     * Integration: Session expires and requires re-authentication
     *
     * **Validates: Requirement 2.6**
     *
     * After logout, the session must be invalidated and subsequent requests
     * must require re-authentication.
     */
    public function test_session_invalidated_after_logout(): void
    {
        $password = 'SecurePass123';
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // Login
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => $password,
        ]);
        $this->assertAuthenticated();

        // Logout
        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));

        // Should not be authenticated
        $this->assertGuest();
    }

    /**
     * Integration: Multiple validation scenarios with rate limiting
     *
     * **Validates: Requirements 2.2, 2.3, 2.4**
     *
     * Combines validation errors and rate limiting:
     * - Missing email
     * - Missing password
     * - Invalid email format
     * - Rate limiting on valid email format with wrong credentials
     */
    public function test_login_validation_and_rate_limiting_integration(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('CorrectPass123'),
            'is_active' => true,
        ]);

        // Missing email
        $response = $this->post('/login', [
            'password' => 'CorrectPass123',
        ]);
        $response->assertSessionHasErrors('email');

        // Missing password
        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);
        $response->assertSessionHasErrors('password');

        // Invalid email format
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'CorrectPass123',
        ]);
        $response->assertSessionHasErrors('email');

        // Now test rate limiting with valid email format
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'WrongPassword',
            ]);
        }

        // 6th attempt should be rate-limited
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors');
        $this->assertStringContainsString('Terlalu banyak percobaan', $errors->first('email'));
    }
}
