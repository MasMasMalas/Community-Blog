<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleThumbnailUploadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Author can create article with thumbnail upload.
     *
     * Requirement 4.5, 18.1, 18.3, 18.4, 18.6
     */
    public function test_author_can_create_article_with_thumbnail_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        $file = UploadedFile::fake()->image('thumbnail.jpg', 800, 600);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article with Thumbnail',
                'excerpt' => 'This is a test article with thumbnail',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file,
            ]);

        $response->assertRedirect();

        $article = Article::where('title', 'Test Article with Thumbnail')->first();
        $this->assertNotNull($article);
        $this->assertNotNull($article->thumbnail);

        // Verify file is stored in articles directory
        Storage::disk('public')->assertExists($article->thumbnail);
    }

    /**
     * Author can create article without thumbnail upload.
     *
     * Requirement 4.5, 18.1
     */
    public function test_author_can_create_article_without_thumbnail(): void
    {
        $user = User::factory()->create(['role' => 'author']);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article without Thumbnail',
                'excerpt' => 'This is a test article without thumbnail',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
            ]);

        $response->assertRedirect();

        $article = Article::where('title', 'Test Article without Thumbnail')->first();
        $this->assertNotNull($article);
        $this->assertNull($article->thumbnail);
    }

    /**
     * Article thumbnail URL accessor returns placeholder when no thumbnail.
     *
     * Requirement 18.1
     */
    public function test_article_thumbnail_url_returns_placeholder_when_no_thumbnail(): void
    {
        $article = Article::factory()->create(['thumbnail' => null]);

        $this->assertStringContainsString('placeholder', $article->thumbnail_url);
    }

    /**
     * Article thumbnail URL accessor returns proper URL when thumbnail exists.
     *
     * Requirement 18.1, 18.6
     */
    public function test_article_thumbnail_url_returns_proper_url_when_thumbnail_exists(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        $file = UploadedFile::fake()->image('thumbnail.jpg', 800, 600);

        $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file,
            ]);

        $article = Article::where('title', 'Test Article')->first();
        $this->assertNotNull($article->thumbnail);
        $this->assertStringContainsString('articles/', $article->thumbnail_url);
        $this->assertStringNotContainsString('placeholder', $article->thumbnail_url);
    }

    /**
     * Author cannot upload thumbnail with invalid MIME type.
     *
     * Requirement 18.3
     */
    public function test_author_cannot_upload_thumbnail_with_invalid_mime_type(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file,
            ]);

        $response->assertSessionHasErrors('thumbnail');
        $this->assertNull(Article::where('title', 'Test Article')->first());
    }

    /**
     * Author cannot upload thumbnail exceeding 5MB size limit.
     *
     * Requirement 18.4
     */
    public function test_author_cannot_upload_thumbnail_exceeding_5mb_limit(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        // Create file larger than 5120 KB
        $file = UploadedFile::fake()->image('thumbnail.jpg', 2048, 2048)->size(6000);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file,
            ]);

        $response->assertSessionHasErrors('thumbnail');
        $this->assertNull(Article::where('title', 'Test Article')->first());
    }

    /**
     * Author can upload JPEG format thumbnail.
     *
     * Requirement 18.3
     */
    public function test_author_can_upload_jpeg_format_thumbnail(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        $file = UploadedFile::fake()->image('thumbnail.jpeg', 800, 600);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file,
            ]);

        $response->assertRedirect();
        $this->assertNotNull(Article::where('title', 'Test Article')->first()->thumbnail);
    }

    /**
     * Author can upload PNG format thumbnail.
     *
     * Requirement 18.3
     */
    public function test_author_can_upload_png_format_thumbnail(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        $file = UploadedFile::fake()->image('thumbnail.png', 800, 600);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file,
            ]);

        $response->assertRedirect();
        $this->assertNotNull(Article::where('title', 'Test Article')->first()->thumbnail);
    }

    /**
     * Author can upload GIF format thumbnail.
     *
     * Requirement 18.3
     */
    public function test_author_can_upload_gif_format_thumbnail(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        $file = UploadedFile::fake()->image('thumbnail.gif', 800, 600);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file,
            ]);

        $response->assertRedirect();
        $this->assertNotNull(Article::where('title', 'Test Article')->first()->thumbnail);
    }

    /**
     * Thumbnail file is stored with UUID-based safe filename.
     *
     * Requirement 18.6
     */
    public function test_thumbnail_file_is_stored_with_uuid_based_filename(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        $file = UploadedFile::fake()->image('thumbnail.jpg', 800, 600);

        $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file,
            ]);

        $article = Article::where('title', 'Test Article')->first();

        // Verify filename is UUID-based (contains hyphens typical of UUIDs)
        $this->assertMatchesRegularExpression(
            '/articles\/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.(jpg|png|gif)/',
            $article->thumbnail
        );
    }

    /**
     * Author can update article with new thumbnail upload.
     *
     * Requirement 5.5, 18.1, 18.3, 18.4, 18.6
     */
    public function test_author_can_update_article_with_new_thumbnail(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        // Create article with initial thumbnail
        $file1 = UploadedFile::fake()->image('thumbnail1.jpg', 800, 600);
        $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file1,
            ]);

        $article = Article::where('title', 'Test Article')->first();
        $oldThumbnail = $article->thumbnail;

        // Update with new thumbnail
        $file2 = UploadedFile::fake()->image('thumbnail2.jpg', 800, 600);
        $this->actingAs($user)
            ->put("/articles/{$article->id}", [
                'title' => $article->title,
                'excerpt' => 'Updated excerpt',
                'content' => 'Updated content with enough characters to pass validation requirements.',
                'thumbnail' => $file2,
            ]);

        $article = $article->fresh();
        $this->assertNotNull($article->thumbnail);
        $this->assertNotEquals($oldThumbnail, $article->thumbnail);

        // Old thumbnail should be deleted
        Storage::disk('public')->assertMissing($oldThumbnail);
    }

    /**
     * Old thumbnail is deleted when replaced.
     *
     * Requirement 18.6, 18.7
     */
    public function test_old_thumbnail_is_deleted_when_replaced(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        // Create article with initial thumbnail
        $file1 = UploadedFile::fake()->image('thumbnail1.jpg', 800, 600);
        $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file1,
            ]);

        $article = Article::where('title', 'Test Article')->first();
        $oldThumbnail = $article->thumbnail;

        // Verify old file exists
        Storage::disk('public')->assertExists($oldThumbnail);

        // Update with new thumbnail
        $file2 = UploadedFile::fake()->image('thumbnail2.jpg', 800, 600);
        $this->actingAs($user)
            ->put("/articles/{$article->id}", [
                'title' => $article->title,
                'excerpt' => 'Updated excerpt',
                'content' => 'Updated content with enough characters to pass validation requirements.',
                'thumbnail' => $file2,
            ]);

        // Old file should be deleted
        Storage::disk('public')->assertMissing($oldThumbnail);
    }

    /**
     * Author can update article without changing thumbnail.
     *
     * Requirement 5.5
     */
    public function test_author_can_update_article_without_changing_thumbnail(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'author']);

        // Create article with initial thumbnail
        $file = UploadedFile::fake()->image('thumbnail.jpg', 800, 600);
        $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                'thumbnail' => $file,
            ]);

        $article = Article::where('title', 'Test Article')->first();
        $existingThumbnail = $article->thumbnail;

        // Update article without thumbnail
        $this->actingAs($user)
            ->put("/articles/{$article->id}", [
                'title' => 'Updated Article Title',
                'excerpt' => 'Updated excerpt',
                'content' => 'Updated content with enough characters to pass validation requirements.',
            ]);

        $article = $article->fresh();
        // Thumbnail should remain unchanged
        $this->assertEquals($existingThumbnail, $article->thumbnail);
    }

    /**
     * Unauthenticated user cannot upload article with thumbnail.
     *
     * Requirement 4.1, 17.4
     */
    public function test_unauthenticated_user_cannot_upload_article_with_thumbnail(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('thumbnail.jpg', 800, 600);

        $response = $this->post('/articles', [
            'title' => 'Test Article',
            'excerpt' => 'Excerpt',
            'content' => 'This is the full content of the test article with enough characters to pass validation.',
            'thumbnail' => $file,
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Article creation validates required fields.
     *
     * Requirement 4.2, 4.9
     */
    public function test_article_creation_validates_required_fields(): void
    {
        $user = User::factory()->create(['role' => 'author']);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => '',
                'excerpt' => '',
                'content' => '',
            ]);

        $response->assertSessionHasErrors(['title', 'content']);
    }

    /**
     * Article creation validates content minimum length.
     *
     * Requirement 4.2
     */
    public function test_article_creation_validates_content_minimum_length(): void
    {
        $user = User::factory()->create(['role' => 'author']);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article',
                'excerpt' => 'Excerpt',
                'content' => 'Short', // Less than 10 characters
            ]);

        $response->assertSessionHasErrors('content');
    }

    /**
     * Thumbnail is optional during article creation.
     *
     * Requirement 4.5
     */
    public function test_thumbnail_is_optional_during_article_creation(): void
    {
        $user = User::factory()->create(['role' => 'author']);

        $response = $this->actingAs($user)
            ->post('/articles', [
                'title' => 'Test Article Without Thumbnail',
                'excerpt' => 'Excerpt',
                'content' => 'This is the full content of the test article with enough characters to pass validation.',
                // No thumbnail provided
            ]);

        $response->assertRedirect();
        $article = Article::where('title', 'Test Article Without Thumbnail')->first();
        $this->assertNotNull($article);
        $this->assertNull($article->thumbnail);
    }
}
