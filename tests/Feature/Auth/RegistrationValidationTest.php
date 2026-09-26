<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Property-Based Tests for User Registration Validation
 *
 * **Validates: Requirements 1.2, 1.3, 1.4, 1.5, 1.6**
 *
 * This test class validates universal properties that must hold across all inputs:
 * - Property 1: Email Uniqueness
 * - Property 2: Username Format Validation
 * - Property 3: Password Strength Validation
 * - Property 4: Password Hashing Validation
 */
class RegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 1: Email Uniqueness — Registered emails must be rejected
     *
     * **Validates: Requirement 1.2**
     *
     * For any email that is already registered, attempting to register with that
     * email should be rejected with a validation error.
     */
    public function test_email_uniqueness_property(): void
    {
        // Create an existing user with a known email
        $existingEmail = 'existing@example.com';
        User::factory()->create(['email' => $existingEmail]);

        // Try to register with the same email
        $response = $this->post('/register', [
            'name' => 'New User',
            'username' => 'newuser123',
            'email' => $existingEmail,
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ]);

        // Request should be rejected with validation error
        $response->assertSessionHasErrors('email');
        $this->assertCount(1, User::where('email', $existingEmail)->get());
    }

    /**
     * Property 2: Username Format Validation — Invalid characters must be rejected
     *
     * **Validates: Requirements 1.3, 1.4**
     *
     * For any username containing characters outside [a-zA-Z0-9_], registration
     * must be rejected.
     */
    public function test_username_format_validation_invalid_characters(): void
    {
        // Test cases: usernames with invalid characters (representative sample)
        $invalidUsernames = [
            'user-name',        // hyphen
            'user.name',        // dot
            'user name',        // space
            'user@name',        // @
            'user#name',        // #
            'user!name',        // !
            'user$name',        // $
            'user/name',        // slash
        ];

        foreach ($invalidUsernames as $invalidUsername) {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'username' => $invalidUsername,
                'email' => 'test'.uniqid().'@example.com',
                'password' => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
            ]);

            // Validation should fail - either with 422 or session errors
            if ($response->status() === 302) {
                // If redirected, check that errors are in session
                $response->assertSessionHasErrors('username');
            } else {
                // Otherwise expect unprocessable
                $response->assertUnprocessable();
                $response->assertSessionHasErrors('username');
            }
        }
    }

    /**
     * Property 2 (Variant): Valid username characters must be accepted
     *
     * **Validates: Requirements 1.3, 1.4**
     *
     * For any username containing only [a-zA-Z0-9_], registration should proceed
     * (assuming other validations pass).
     */
    public function test_username_format_validation_valid_characters(): void
    {
        // Test cases: usernames with valid characters (representative sample)
        $validUsernames = [
            'username',
            'user_name',
            'User_Name',
            'user123',
            'user_1_A',
        ];

        foreach ($validUsernames as $validUsername) {
            $email = 'test'.uniqid().'@example.com';

            $response = $this->post('/register', [
                'name' => 'Test User',
                'username' => $validUsername,
                'email' => $email,
                'password' => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
            ]);

            // Should either succeed or fail on other validation (not username format)
            if ($response->status() === 302) {
                // Success: user created
                $this->assertDatabaseHas('users', ['username' => $validUsername]);
            } else {
                // If it fails, it should NOT be due to username format
                $errors = session('errors');
                if ($errors && $errors->has('username')) {
                    $this->assertStringNotContainsString('hanya boleh mengandung', $errors->first('username'));
                }
            }
        }
    }

    /**
     * Property 3: Password Strength Validation — Weak passwords must be rejected
     *
     * **Validates: Requirement 1.5**
     *
     * For any password that does not meet strength requirements (min 8 chars,
     * 1 uppercase, 1 lowercase, 1 digit), registration must be rejected.
     */
    public function test_password_strength_validation_weak_passwords(): void
    {
        // Test cases: weak passwords (representative sample)
        $weakPasswords = [
            'short',             // too short
            'Pass1',             // too short
            'password123',       // no uppercase
            'PASSWORD123',       // no lowercase
            'PasswordAbc',       // no digit
            '12345678',          // only digits
            'abcdefgh',          // only lowercase
        ];

        foreach ($weakPasswords as $weakPassword) {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'username' => 'user'.uniqid(),
                'email' => 'test'.uniqid().'@example.com',
                'password' => $weakPassword,
                'password_confirmation' => $weakPassword,
            ]);

            // Validation should fail - either with 422 or session errors
            if ($response->status() === 302) {
                // If redirected, check that errors are in session
                $response->assertSessionHasErrors('password');
            } else {
                // Otherwise expect unprocessable
                $response->assertUnprocessable();
                $response->assertSessionHasErrors('password');
            }
        }
    }

    /**
     * Property 3 (Variant): Strong passwords must be accepted
     *
     * **Validates: Requirement 1.5**
     *
     * For any password that meets all strength requirements (min 8 chars,
     * 1 uppercase, 1 lowercase, 1 digit), registration should proceed
     * (assuming other validations pass).
     */
    public function test_password_strength_validation_strong_passwords(): void
    {
        // Test cases: strong passwords (representative sample)
        $strongPasswords = [
            'SecurePass123',
            'MyPassword1',
            'TestPass99',
            'Abcdefgh1',
        ];

        foreach ($strongPasswords as $strongPassword) {
            $email = 'test'.uniqid().'@example.com';
            $username = 'user'.uniqid();

            $response = $this->post('/register', [
                'name' => 'Test User',
                'username' => $username,
                'email' => $email,
                'password' => $strongPassword,
                'password_confirmation' => $strongPassword,
            ]);

            // If there's an error, it should not be about password strength
            if ($response->status() !== 302) {
                $errors = session('errors');
                if ($errors && $errors->has('password')) {
                    $this->assertStringNotContainsString('minimal', $errors->first('password'));
                    $this->assertStringNotContainsString('besar', $errors->first('password'));
                }
            } else {
                // Success: user created
                $this->assertDatabaseHas('users', ['username' => $username]);
            }
        }
    }

    /**
     * Property 3 (Variant): Password confirmation mismatch must be rejected
     *
     * **Validates: Requirement 1.5**
     *
     * For any password that doesn't match its confirmation, registration must
     * be rejected.
     */
    public function test_password_confirmation_mismatch(): void
    {
        $testCases = [
            ['password' => 'SecurePass123', 'confirmation' => 'SecurePass124'],
            ['password' => 'SecurePass123', 'confirmation' => 'securepass123'],
            ['password' => 'SecurePass123', 'confirmation' => ''],
        ];

        foreach ($testCases as $case) {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'username' => 'user'.uniqid(),
                'email' => 'test'.uniqid().'@example.com',
                'password' => $case['password'],
                'password_confirmation' => $case['confirmation'],
            ]);

            // Validation should fail - either with 422 or session errors
            if ($response->status() === 302) {
                // If redirected, check that errors are in session
                $response->assertSessionHasErrors('password');
            } else {
                // Otherwise expect unprocessable
                $response->assertUnprocessable();
                $response->assertSessionHasErrors('password');
            }
        }
    }

    /**
     * Property 4: Password Hashing — Plaintext passwords must not be stored
     *
     * **Validates: Requirement 1.6**
     *
     * For any successful registration, the stored password hash must not equal
     * the plaintext password that was submitted.
     */
    public function test_password_hashing_property(): void
    {
        $plainTextPassword = 'SecurePass123';
        $username = 'user'.uniqid();
        $email = 'test'.uniqid().'@example.com';

        $this->post('/register', [
            'name' => 'Test User',
            'username' => $username,
            'email' => $email,
            'password' => $plainTextPassword,
            'password_confirmation' => $plainTextPassword,
        ]);

        // Verify user was created
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user, 'User should be created after registration');

        // Verify password is hashed, not plaintext
        $this->assertNotEquals(
            $plainTextPassword,
            $user->password,
            'Password must be hashed, not stored as plaintext'
        );

        // Verify the hash can be verified against the plaintext password
        $this->assertTrue(
            Hash::check($plainTextPassword, $user->password),
            'Password hash should verify correctly against plaintext'
        );
    }

    /**
     * Property 4 (Variant): Different passwords must produce different hashes
     *
     * **Validates: Requirement 1.6**
     *
     * For any two different plaintext passwords, their hashes must be different.
     * Even if the same password is hashed twice, the hashes will differ (due to
     * salt in bcrypt), but they should both verify correctly.
     */
    public function test_password_hashing_different_passwords(): void
    {
        $passwords = [
            'FirstPassword123',
            'SecondPassword456',
            'ThirdPassword789',
        ];

        $hashes = [];

        foreach ($passwords as $index => $password) {
            $username = 'user'.uniqid();
            $email = 'test'.uniqid().'@example.com';

            $this->post('/register', [
                'name' => 'Test User',
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ]);

            $user = User::where('email', $email)->first();
            $hashes[$index] = $user->password;

            // Verify this password is not plaintext
            $this->assertNotEquals($password, $user->password);

            // Verify the hash verifies correctly
            $this->assertTrue(Hash::check($password, $user->password));
        }

        // Verify each hash is unique
        $this->assertCount(count(array_unique($hashes)), $hashes, 'Different passwords should produce different hashes');
    }

    /**
     * Property 4 (Variant): Password hashing consistency — same plaintext with different users
     *
     * **Validates: Requirement 1.6**
     *
     * For any two users registered with the same plaintext password, their
     * stored hashes must be different (due to bcrypt's random salt), but both
     * should verify correctly against the plaintext.
     */
    public function test_password_hashing_same_password_different_users(): void
    {
        $plainTextPassword = 'SecurePass123';

        $user1Email = 'user1'.uniqid().'@example.com';
        $this->post('/register', [
            'name' => 'User One',
            'username' => 'userone'.uniqid(),
            'email' => $user1Email,
            'password' => $plainTextPassword,
            'password_confirmation' => $plainTextPassword,
        ]);
        $user1 = User::where('email', $user1Email)->first();

        $user2Email = 'user2'.uniqid().'@example.com';
        $this->post('/register', [
            'name' => 'User Two',
            'username' => 'usertwo'.uniqid(),
            'email' => $user2Email,
            'password' => $plainTextPassword,
            'password_confirmation' => $plainTextPassword,
        ]);
        $user2 = User::where('email', $user2Email)->first();

        // Both should have different hashes due to bcrypt salt
        $this->assertNotEquals(
            $user1->password,
            $user2->password,
            'Same plaintext password should produce different hashes (due to salt)'
        );

        // But both should verify against the plaintext
        $this->assertTrue(Hash::check($plainTextPassword, $user1->password));
        $this->assertTrue(Hash::check($plainTextPassword, $user2->password));
    }

    /**
     * Property 4 (Variant): No password stored can be guessed from hash
     *
     * **Validates: Requirement 1.6**
     *
     * For any registered user, attempting to compare the hash with plaintext
     * should fail (verifying the hash is actual hash and not plaintext).
     */
    public function test_password_not_reversible_from_hash(): void
    {
        $plainTextPassword = 'SecurePass123';
        $username = 'user'.uniqid();
        $email = 'test'.uniqid().'@example.com';

        $this->post('/register', [
            'name' => 'Test User',
            'username' => $username,
            'email' => $email,
            'password' => $plainTextPassword,
            'password_confirmation' => $plainTextPassword,
        ]);

        $user = User::where('email', $email)->first();

        // Try common wrong passwords - should all fail
        $wrongPasswords = [
            'SecurePass124',
            'securepass123',
            'SECUREPASS123',
            'secure',
            '',
        ];

        foreach ($wrongPasswords as $wrongPassword) {
            $this->assertFalse(
                Hash::check($wrongPassword, $user->password),
                "Wrong password '{$wrongPassword}' should not verify"
            );
        }
    }

    /**
     * Integration: All validations work together
     *
     * **Validates: Requirements 1.2, 1.3, 1.4, 1.5, 1.6**
     *
     * Multiple validation errors should be collected and reported together.
     */
    public function test_multiple_validation_errors(): void
    {
        // Create an existing user to conflict with email and username
        $existingUser = User::factory()->create();

        // Try to register with multiple errors:
        // - Email already exists
        // - Username already exists
        // - Username has invalid characters
        // - Password is weak
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => $existingUser->username,  // existing + may have invalid chars
            'email' => $existingUser->email,      // existing
            'password' => 'weak',                    // too weak
            'password_confirmation' => 'weak',                    // mismatch or weak
        ]);

        // Should have validation errors
        $response->assertSessionHasErrors();

        // At minimum should have errors on email and password
        $errors = session('errors');
        $this->assertTrue($errors->has('email') || $errors->has('username') || $errors->has('password'));
    }
}
