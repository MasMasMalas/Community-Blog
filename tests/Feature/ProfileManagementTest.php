<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticated user can view their profile.
     *
     * Persyaratan: 3.1, 3.4
     */
    public function test_authenticated_user_can_view_their_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'bio' => 'Software developer from Indonesia',
        ]);

        $response = $this->actingAs($user)
            ->get('/profile');

        $response->assertStatus(200);
        $response->assertViewIs('profile.show');
        $response->assertViewHas('user', $user);
        $response->assertSee('John Doe');
        $response->assertSee('johndoe');
        $response->assertSee('john@example.com');
        $response->assertSee('Software developer from Indonesia');
    }

    /**
     * Unauthenticated user cannot view profile.
     *
     * Persyaratan: 2.7, 17.4
     */
    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    /**
     * Authenticated user can view edit profile form.
     *
     * Persyaratan: 3.1
     */
    public function test_authenticated_user_can_view_edit_profile_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
        $response->assertViewHas('user', $user);
    }

    /**
     * User can update their profile with valid data.
     *
     * Persyaratan: 3.1, 3.2, 3.3, 3.4
     */
    public function test_user_can_update_profile_with_valid_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'username' => 'oldusername',
            'bio' => 'Old bio',
        ]);

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => 'New Name',
                'username' => 'newusername',
                'email' => $user->email,
                'bio' => 'New bio that is updated',
            ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success', 'Profil berhasil diperbarui.');

        $updatedUser = $user->fresh();
        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('newusername', $updatedUser->username);
        $this->assertEquals('New bio that is updated', $updatedUser->bio);
    }

    /**
     * User cannot update profile with duplicate username.
     *
     * Persyaratan: 3.2
     */
    public function test_user_cannot_update_profile_with_duplicate_username(): void
    {
        $user1 = User::factory()->create(['username' => 'user1']);
        $user2 = User::factory()->create(['username' => 'user2']);

        $response = $this->actingAs($user2)
            ->put('/profile', [
                'name' => $user2->name,
                'username' => 'user1', // Username of user1
                'email' => $user2->email,
                'bio' => $user2->bio,
            ]);

        $response->assertSessionHasErrors('username');
        $this->assertEquals('user2', $user2->fresh()->username);
    }

    /**
     * User cannot update profile with duplicate email.
     *
     * Persyaratan: 3.2
     */
    public function test_user_cannot_update_profile_with_duplicate_email(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($user2)
            ->put('/profile', [
                'name' => $user2->name,
                'username' => $user2->username,
                'email' => 'user1@example.com', // Email of user1
                'bio' => $user2->bio,
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('user2@example.com', $user2->fresh()->email);
    }

    /**
     * User cannot update profile with invalid username format.
     *
     * Persyaratan: 3.2
     */
    public function test_user_cannot_update_profile_with_invalid_username_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => 'user@name', // Invalid character '@'
                'email' => $user->email,
                'bio' => $user->bio,
            ]);

        $response->assertSessionHasErrors('username');
    }

    /**
     * User cannot update profile with bio exceeding 300 characters.
     *
     * Persyaratan: 3.3
     */
    public function test_user_cannot_update_profile_with_bio_exceeding_limit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => str_repeat('a', 301), // 301 characters, exceeds 300 limit
            ]);

        $response->assertSessionHasErrors('bio');
    }

    /**
     * User can update profile with exactly 300 character bio.
     *
     * Persyaratan: 3.3, 3.4
     */
    public function test_user_can_update_profile_with_300_character_bio(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => str_repeat('a', 300), // Exactly 300 characters
            ]);

        $response->assertRedirect('/profile');
        $this->assertEquals(str_repeat('a', 300), $user->fresh()->bio);
    }

    /**
     * User can update profile with empty bio.
     *
     * Persyaratan: 3.3, 3.4
     */
    public function test_user_can_update_profile_with_empty_bio(): void
    {
        $user = User::factory()->create(['bio' => 'Previous bio']);

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => '', // Empty string instead of null
            ]);

        $response->assertRedirect('/profile');
        $this->assertNull($user->fresh()->bio);
    }

    /**
     * User cannot update profile with missing required fields.
     *
     * Persyaratan: 3.1, 3.2
     */
    public function test_user_cannot_update_profile_with_missing_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => '', // Missing name
                'username' => '',
                'email' => '',
                'bio' => $user->bio,
            ]);

        $response->assertSessionHasErrors(['name', 'username', 'email']);
    }

    /**
     * User can update profile with avatar upload.
     *
     * Persyaratan: 3.5, 3.6, 18.1, 18.2, 18.3, 18.4, 18.5, 18.6, 18.7, 18.8
     */
    public function test_user_can_update_profile_with_avatar_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar' => $file,
            ]);

        $response->assertRedirect('/profile');

        $updatedUser = $user->fresh();
        $this->assertNotNull($updatedUser->avatar);
        // Verify file is stored in avatars directory
        Storage::disk('public')->assertExists($updatedUser->avatar);
    }

    /**
     * User cannot upload avatar with invalid file type.
     *
     * Persyaratan: 3.5, 18.3, 18.4
     */
    public function test_user_cannot_upload_avatar_with_invalid_file_type(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar' => $file,
            ]);

        $response->assertSessionHasErrors('avatar');
    }

    /**
     * User cannot upload avatar exceeding size limit.
     *
     * Persyaratan: 3.5, 18.4
     */
    public function test_user_cannot_upload_avatar_exceeding_size_limit(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('avatar.jpg', 2048, 2048)->size(3000); // 3000 KB exceeds 2 MB limit

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar' => $file,
            ]);

        $response->assertSessionHasErrors('avatar');
    }

    /**
     * User can upload PNG format avatar.
     *
     * Persyaratan: 3.5, 3.6, 18.3
     */
    public function test_user_can_upload_png_format_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('avatar.png', 100, 100);

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar' => $file,
            ]);

        $response->assertRedirect('/profile');
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    /**
     * User can upload GIF format avatar.
     *
     * Persyaratan: 3.5, 3.6, 18.3
     */
    public function test_user_can_upload_gif_format_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('avatar.gif', 100, 100);

        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar' => $file,
            ]);

        $response->assertRedirect('/profile');
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    /**
     * User avatar is replaced when uploading new one.
     *
     * Persyaratan: 3.5, 3.6, 18.6, 18.7
     */
    public function test_user_avatar_is_replaced_when_uploading_new_one(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Upload first avatar
        $file1 = UploadedFile::fake()->image('avatar1.jpg', 100, 100);
        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar' => $file1,
            ]);

        $oldAvatar = $user->fresh()->avatar;
        $this->assertNotNull($oldAvatar);

        // Upload second avatar
        $file2 = UploadedFile::fake()->image('avatar2.jpg', 100, 100);
        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar' => $file2,
            ]);

        $newAvatar = $user->fresh()->avatar;
        $this->assertNotNull($newAvatar);
        $this->assertNotEquals($oldAvatar, $newAvatar);
        // Old avatar should be deleted
        Storage::disk('public')->assertMissing($oldAvatar);
    }

    /**
     * User can update profile without changing avatar.
     *
     * Persyaratan: 3.1, 3.4
     */
    public function test_user_can_update_profile_without_changing_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Upload avatar first
        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);
        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar' => $file,
            ]);

        $existingAvatar = $user->fresh()->avatar;

        // Update profile without avatar
        $response = $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Updated Name',
                'username' => $user->username,
                'email' => $user->email,
                'bio' => 'Updated bio',
            ]);

        $response->assertRedirect('/profile');
        // Avatar should remain unchanged
        $this->assertEquals($existingAvatar, $user->fresh()->avatar);
    }

    /**
     * Profile page displays all required user information.
     *
     * Persyaratan: 3.1, 3.4
     */
    public function test_profile_page_displays_all_required_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'bio' => 'Test bio',
            'role' => 'author',
        ]);

        $response = $this->actingAs($user)
            ->get('/profile');

        $response->assertSee('Test User'); // name
        $response->assertSee('testuser'); // username
        $response->assertSee('test@example.com'); // email
        $response->assertSee('Test bio'); // bio
        $response->assertSee('Author'); // role
    }

    /**
     * Authenticated user can access change password form.
     *
     * Persyaratan: 3.7, 3.8
     */
    public function test_authenticated_user_can_access_change_password_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/profile/password');

        $response->assertStatus(200);
        $response->assertViewIs('profile.change-password');
        $response->assertSee('Ubah Kata Sandi');
    }

    /**
     * Unauthenticated user cannot access change password form.
     *
     * Persyaratan: 2.7, 17.4, 3.7
     */
    public function test_unauthenticated_user_cannot_access_change_password_form(): void
    {
        $response = $this->get('/profile/password');

        $response->assertRedirect('/login');
    }

    /**
     * User can change password with valid data.
     *
     * Persyaratan: 3.7, 3.8
     */
    public function test_user_can_change_password_with_valid_data(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'OldPassword123',
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
            ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success', 'Kata sandi Anda berhasil diubah.');

        // Verify password was actually changed
        $this->assertTrue(Hash::check('NewPassword456', $user->fresh()->password));
        $this->assertFalse(Hash::check('OldPassword123', $user->fresh()->password));
    }

    /**
     * User cannot change password with incorrect current password.
     *
     * Persyaratan: 3.7
     */
    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'WrongPassword123',
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
            ]);

        $response->assertSessionHasErrors('current_password');
        $response->assertSessionHas('errors');

        // Verify password was not changed
        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
    }

    /**
     * User cannot change password with mismatched password confirmation.
     *
     * Persyaratan: 3.8
     */
    public function test_user_cannot_change_password_with_mismatched_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'OldPassword123',
                'password' => 'NewPassword456',
                'password_confirmation' => 'DifferentPassword456',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User cannot change password with password less than 8 characters.
     *
     * Persyaratan: 3.8 - password strength: min 8 chars
     */
    public function test_user_cannot_change_password_with_less_than_8_characters(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'OldPassword123',
                'password' => 'Pass12',
                'password_confirmation' => 'Pass12',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User cannot change password without uppercase letter.
     *
     * Persyaratan: 3.8 - password strength: min 1 uppercase
     */
    public function test_user_cannot_change_password_without_uppercase(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'OldPassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User cannot change password without lowercase letter.
     *
     * Persyaratan: 3.8 - password strength: min 1 lowercase
     */
    public function test_user_cannot_change_password_without_lowercase(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'OldPassword123',
                'password' => 'NEWPASSWORD123',
                'password_confirmation' => 'NEWPASSWORD123',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User cannot change password without digit.
     *
     * Persyaratan: 3.8 - password strength: min 1 digit
     */
    public function test_user_cannot_change_password_without_digit(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'OldPassword123',
                'password' => 'NewPasswordTest',
                'password_confirmation' => 'NewPasswordTest',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * User cannot change password with missing current password.
     *
     * Persyaratan: 3.7
     */
    public function test_user_cannot_change_password_with_missing_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => '',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    }

    /**
     * User cannot change password with missing new password.
     *
     * Persyaratan: 3.8
     */
    public function test_user_cannot_change_password_with_missing_new_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'OldPassword123',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Unauthenticated user cannot update password.
     *
     * Persyaratan: 2.7, 17.4, 3.7
     */
    public function test_unauthenticated_user_cannot_update_password(): void
    {
        $response = $this->put('/profile/password', [
            'current_password' => 'SomePassword123',
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * New password must be different from current password validation.
     *
     * Persyaratan: 3.8
     */
    public function test_user_can_change_password_to_different_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123'),
        ]);

        // Verify old password works
        $this->assertTrue(Hash::check('OldPassword123', $user->password));

        // Change to new password
        $response = $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'OldPassword123',
                'password' => 'CompletelyNewPassword123',
                'password_confirmation' => 'CompletelyNewPassword123',
            ]);

        $response->assertRedirect('/profile');

        // Verify new password is set
        $updatedUser = $user->fresh();
        $this->assertTrue(Hash::check('CompletelyNewPassword123', $updatedUser->password));
        $this->assertFalse(Hash::check('OldPassword123', $updatedUser->password));
    }
}
