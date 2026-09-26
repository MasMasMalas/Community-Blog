<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticated user can view the change password form.
     *
     * Persyaratan: 3.7, 3.8
     */
    public function test_authenticated_user_can_view_change_password_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/profile/password');

        $response->assertStatus(200);
        $response->assertViewIs('profile.change-password');
    }

    /**
     * Unauthenticated user cannot view the change password form.
     *
     * Persyaratan: 2.7, 17.2
     */
    public function test_unauthenticated_user_cannot_view_change_password_form(): void
    {
        $response = $this->get('/profile/password');

        $response->assertRedirect('/login');
    }

    /**
     * User cannot change password without providing current password.
     *
     * Persyaratan: 3.7
     */
    public function test_user_cannot_change_password_without_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => '',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('CurrentPassword123', $user->fresh()->password));
    }

    /**
     * User cannot change password with incorrect current password.
     *
     * Persyaratan: 3.7
     */
    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'WrongPassword123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('CurrentPassword123', $user->fresh()->password));
    }

    /**
     * User cannot change password with weak new password (less than 8 characters).
     *
     * Persyaratan: 3.8
     */
    public function test_user_cannot_change_password_with_weak_password_too_short(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'CurrentPassword123',
                'password' => 'Short1A',
                'password_confirmation' => 'Short1A',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User cannot change password without uppercase letters.
     *
     * Persyaratan: 3.8
     */
    public function test_user_cannot_change_password_without_uppercase(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'CurrentPassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User cannot change password without lowercase letters.
     *
     * Persyaratan: 3.8
     */
    public function test_user_cannot_change_password_without_lowercase(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'CurrentPassword123',
                'password' => 'NEWPASSWORD123',
                'password_confirmation' => 'NEWPASSWORD123',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User cannot change password without numbers.
     *
     * Persyaratan: 3.8
     */
    public function test_user_cannot_change_password_without_numbers(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'CurrentPassword123',
                'password' => 'NewPassword',
                'password_confirmation' => 'NewPassword',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User cannot change password with mismatched confirmation.
     *
     * Persyaratan: 1.5
     */
    public function test_user_cannot_change_password_with_mismatched_confirmation(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'CurrentPassword123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'DifferentPassword123',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User can successfully change password with valid input.
     *
     * Persyaratan: 3.7, 3.8
     */
    public function test_user_can_change_password_with_valid_input(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'CurrentPassword123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success', 'Kata sandi Anda berhasil diubah.');

        // Verify password is updated and hashed
        $updatedUser = $user->fresh();
        $this->assertTrue(Hash::check('NewPassword123', $updatedUser->password));
        $this->assertFalse(Hash::check('CurrentPassword123', $updatedUser->password));
    }

    /**
     * User can login with new password after change.
     *
     * Persyaratan: 3.7, 3.8, 2.2
     */
    public function test_user_can_login_with_new_password_after_change(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        // Change password
        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'CurrentPassword123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        // Logout
        $this->post('/logout');

        // Try to login with new password
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'NewPassword123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user->fresh());
    }

    /**
     * User cannot login with old password after change.
     *
     * Persyaratan: 3.7, 3.8
     */
    public function test_user_cannot_login_with_old_password_after_change(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        // Change password
        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'CurrentPassword123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        // Logout
        $this->post('/logout');

        // Try to login with old password
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'CurrentPassword123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Password is stored as hash, not plaintext.
     *
     * Persyaratan: 3.8
     */
    public function test_password_is_stored_as_hash(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPassword123')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'CurrentPassword123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $updatedUser = $user->fresh();

        // Password should never be stored as plaintext
        $this->assertNotEquals('NewPassword123', $updatedUser->password);
        // Password should be hashed
        $this->assertTrue(Hash::check('NewPassword123', $updatedUser->password));
    }
}
