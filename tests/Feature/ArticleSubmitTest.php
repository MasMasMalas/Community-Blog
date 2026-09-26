<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleSubmitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that author can submit article with valid title and content.
     * Requirement 6.1, 6.4
     */
    public function test_author_can_submit_article_with_valid_data(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Valid Title',
                'content' => str_repeat('This is content. ', 10), // > 100 chars
            ]);

        $response = $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title',
            'content' => str_repeat('This is content. ', 10),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Artikel berhasil dikirim untuk ditinjau.');

        $article->refresh();
        $this->assertEquals(Article::PENDING_REVIEW, $article->status);
        $this->assertNotNull($article->submitted_at);
    }

    /**
     * Test cannot submit with empty title.
     * Requirement 6.2, 6.5
     */
    public function test_cannot_submit_with_empty_title(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Original Title',
                'content' => str_repeat('This is content. ', 10),
            ]);

        $response = $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => '',
            'content' => str_repeat('This is content. ', 10),
        ]);

        $response->assertSessionHasErrors('title');

        $article->refresh();
        $this->assertEquals(Article::DRAFT, $article->status);
    }

    /**
     * Test cannot submit with whitespace-only title.
     * Requirement 6.2, 6.5
     */
    public function test_cannot_submit_with_whitespace_only_title(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Original Title',
                'content' => str_repeat('This is content. ', 10),
            ]);

        $response = $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => '   ',
            'content' => str_repeat('This is content. ', 10),
        ]);

        $response->assertSessionHasErrors('title');

        $article->refresh();
        $this->assertEquals(Article::DRAFT, $article->status);
    }

    /**
     * Test cannot submit with content less than 100 characters.
     * Requirement 6.3, 6.5
     */
    public function test_cannot_submit_with_content_less_than_100_chars(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Valid Title',
                'content' => 'Short content',
            ]);

        $response = $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title',
            'content' => 'This is only 99 chars xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        ]);

        $response->assertSessionHasErrors('content');

        $article->refresh();
        $this->assertEquals(Article::DRAFT, $article->status);
    }

    /**
     * Test can submit with exactly 100 characters content.
     * Requirement 6.3
     */
    public function test_can_submit_with_exactly_100_chars_content(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Valid Title',
                'content' => 'x',
            ]);

        // Generate exactly 100 characters
        $content = str_repeat('a', 100);

        $response = $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title',
            'content' => $content,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $article->refresh();
        $this->assertEquals(Article::PENDING_REVIEW, $article->status);
    }

    /**
     * Test status changes to PENDING_REVIEW on successful submit.
     * Requirement 6.4
     */
    public function test_status_changes_to_pending_review_on_submit(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Valid Title',
                'content' => str_repeat('content ', 20),
            ]);

        $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title',
            'content' => str_repeat('content ', 20),
        ]);

        $article->refresh();
        $this->assertTrue($article->isPendingReview());
    }

    /**
     * Test submission time is recorded.
     * Requirement 6.7
     */
    public function test_submission_time_is_recorded(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Valid Title',
                'content' => str_repeat('content ', 20),
                'submitted_at' => null,
            ]);

        $before = now();

        $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title',
            'content' => str_repeat('content ', 20),
        ]);

        $after = now();

        $article->refresh();
        $this->assertNotNull($article->submitted_at);
        $this->assertTrue($article->submitted_at->isBetween($before, $after));
    }

    /**
     * Test only owner can submit article.
     * Requirement 6.1
     */
    public function test_only_owner_can_submit_article(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $other = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Valid Title',
                'content' => str_repeat('content ', 20),
            ]);

        $response = $this->actingAs($other)->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title',
            'content' => str_repeat('content ', 20),
        ]);

        $response->assertForbidden();

        $article->refresh();
        $this->assertEquals(Article::DRAFT, $article->status);
    }

    /**
     * Test non-authenticated user cannot submit article.
     */
    public function test_unauthenticated_user_cannot_submit(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Valid Title',
                'content' => str_repeat('content ', 20),
            ]);

        $response = $this->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title',
            'content' => str_repeat('content ', 20),
        ]);

        $response->assertRedirect(route('login'));

        $article->refresh();
        $this->assertEquals(Article::DRAFT, $article->status);
    }

    /**
     * Test can submit article that is already in pending review status.
     * Requirement 6.1
     */
    public function test_can_resubmit_pending_review_article(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::PENDING_REVIEW,
                'title' => 'Valid Title',
                'content' => str_repeat('content ', 20),
                'submitted_at' => now()->subDay(),
            ]);

        $initialSubmissionTime = $article->submitted_at;

        $response = $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title Updated',
            'content' => str_repeat('content updated ', 20),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $article->refresh();
        $this->assertEquals(Article::PENDING_REVIEW, $article->status);
        // Submission time should be updated
        $this->assertTrue($article->submitted_at->isAfter($initialSubmissionTime));
    }

    /**
     * Test cannot submit published article.
     */
    public function test_cannot_submit_published_article(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::PUBLISHED,
                'title' => 'Published Title',
                'content' => str_repeat('content ', 20),
                'published_at' => now(),
            ]);

        $response = $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => 'Published Title',
            'content' => str_repeat('content ', 20),
        ]);

        $response->assertSessionHas('error');
        $response->assertRedirect();

        $article->refresh();
        $this->assertEquals(Article::PUBLISHED, $article->status);
    }

    /**
     * Test cannot submit rejected article (should be in revision status first).
     */
    public function test_cannot_submit_rejected_article(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::REJECTED,
                'title' => 'Rejected Title',
                'content' => str_repeat('content ', 20),
            ]);

        $response = $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => 'Rejected Title',
            'content' => str_repeat('content ', 20),
        ]);

        $response->assertSessionHas('error');

        $article->refresh();
        $this->assertEquals(Article::REJECTED, $article->status);
    }

    /**
     * Test moderator can submit article on behalf of owner.
     */
    public function test_moderator_can_submit_article(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $moderator = User::factory()->create(['role' => 'moderator']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Valid Title',
                'content' => str_repeat('content ', 20),
            ]);

        $response = $this->actingAs($moderator)->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title',
            'content' => str_repeat('content ', 20),
        ]);

        $response->assertRedirect();

        $article->refresh();
        $this->assertEquals(Article::PENDING_REVIEW, $article->status);
    }

    /**
     * Test admin can submit article on behalf of owner.
     */
    public function test_admin_can_submit_article(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::DRAFT,
                'title' => 'Valid Title',
                'content' => str_repeat('content ', 20),
            ]);

        $response = $this->actingAs($admin)->post(route('articles.submit', $article->id), [
            'title' => 'Valid Title',
            'content' => str_repeat('content ', 20),
        ]);

        $response->assertRedirect();

        $article->refresh();
        $this->assertEquals(Article::PENDING_REVIEW, $article->status);
    }

    /**
     * Test validation error messages are in Indonesian.
     */
    public function test_validation_error_messages_are_in_indonesian(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($author)
            ->create(['status' => Article::DRAFT]);

        $response = $this->actingAs($author)->post(route('articles.submit', $article->id), [
            'title' => '',
            'content' => 'short',
        ]);

        $response->assertSessionHasErrors(['title', 'content']);
        $this->assertStringContainsString('Judul wajib diisi', session('errors')->first('title'));
        $this->assertStringContainsString('minimal harus 100 karakter', session('errors')->first('content'));
    }
}
