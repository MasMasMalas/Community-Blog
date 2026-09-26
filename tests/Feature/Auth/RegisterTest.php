<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature tests for task 3.1 — User Registration
 *
 * Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9
 */
class RegisterTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'username' => 'budi_santoso',
            'email' => 'budi@contoh.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Req 1.1 — Registration form is accessible to guests
    // -------------------------------------------------------------------------

    public function test_register_form_is_shown_to_guests(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Daftar');
        $response->assertSee('name');
        $response->assertSee('username');
        $response->assertSee('email');
        $response->assertSee('password');
    }

    // -------------------------------------------------------------------------
    // Req 1.9 — Successful registration redirects to /login
    // -------------------------------------------------------------------------

    public function test_valid_registration_redirects_to_login(): void
    {
        $response = $this->post(route('register'), $this->validPayload());

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // Req 1.7 — New account has role 'author' by default
    // -------------------------------------------------------------------------

    public function test_new_user_gets_author_role(): void
    {
        $this->post(route('register'), $this->validPayload());

        $user = User::where('email', 'budi@contoh.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('author', $user->role);
        $this->assertTrue($user->is_active);
    }

    // -------------------------------------------------------------------------
    // Req 1.6 — Password is stored as a hash (not plaintext)
    // -------------------------------------------------------------------------

    public function test_password_is_stored_as_hash(): void
    {
        $this->post(route('register'), $this->validPayload());

        $user = User::where('email', 'budi@contoh.com')->first();

        $this->assertNotSame('Password1', $user->password);
        $this->assertTrue(Hash::check('Password1', $user->password));
    }

    // -------------------------------------------------------------------------
    // Req 1.2 — Duplicate email is rejected
    // -------------------------------------------------------------------------

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'budi@contoh.com']);

        $response = $this->post(route('register'), $this->validPayload());

        $response->assertSessionHasErrors('email');
        $this->assertCount(1, User::where('email', 'budi@contoh.com')->get());
    }

    // -------------------------------------------------------------------------
    // Req 1.3 — Duplicate username is rejected
    // -------------------------------------------------------------------------

    public function test_duplicate_username_is_rejected(): void
    {
        User::factory()->create(['username' => 'budi_santoso']);

        $response = $this->post(route('register'), $this->validPayload());

        $response->assertSessionHasErrors('username');
    }

    // -------------------------------------------------------------------------
    // Req 1.4 — Username must match [a-zA-Z0-9_]
    // -------------------------------------------------------------------------

    public function test_username_with_special_chars_is_rejected(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'username' => 'budi-santoso!',
        ]));

        $response->assertSessionHasErrors('username');
    }

    public function test_username_too_short_is_rejected(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'username' => 'ab',
        ]));

        $response->assertSessionHasErrors('username');
    }

    public function test_username_too_long_is_rejected(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'username' => str_repeat('a', 21),
        ]));

        $response->assertSessionHasErrors('username');
    }

    public function test_valid_username_with_underscore_is_accepted(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'username' => 'Budi_123',
        ]));

        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Req 1.5 — Password and confirmation must match
    // -------------------------------------------------------------------------

    public function test_mismatched_password_confirmation_is_rejected(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'password_confirmation' => 'DifferentPass2',
        ]));

        $response->assertSessionHasErrors('password');
    }

    // -------------------------------------------------------------------------
    // Req 1.6 — Password must meet strength requirements
    // -------------------------------------------------------------------------

    public function test_password_without_uppercase_is_rejected(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ]));

        $response->assertSessionHasErrors('password');
    }

    public function test_password_without_lowercase_is_rejected(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'password' => 'PASSWORD1',
            'password_confirmation' => 'PASSWORD1',
        ]));

        $response->assertSessionHasErrors('password');
    }

    public function test_password_without_number_is_rejected(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'password' => 'Passwordonly',
            'password_confirmation' => 'Passwordonly',
        ]));

        $response->assertSessionHasErrors('password');
    }

    public function test_password_shorter_than_eight_chars_is_rejected(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'password' => 'Pass1',
            'password_confirmation' => 'Pass1',
        ]));

        $response->assertSessionHasErrors('password');
    }

    // -------------------------------------------------------------------------
    // Req 1.8 — Required fields show errors when blank
    // -------------------------------------------------------------------------

    public function test_all_fields_are_required(): void
    {
        $response = $this->post(route('register'), []);

        $response->assertSessionHasErrors(['name', 'username', 'email', 'password']);
    }

    // -------------------------------------------------------------------------
    // Req 2.7 — Authenticated user is redirected away from the register form
    // -------------------------------------------------------------------------

    public function test_authenticated_user_is_redirected_from_register_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect();
    }
}
