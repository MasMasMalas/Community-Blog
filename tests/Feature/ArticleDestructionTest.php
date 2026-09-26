<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleDestructionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake storage to avoid actual file operations
        Storage::fake('public');
    }

    /**
     * Test owner can delete their own article.
     *
     * Validates Requirement 5.7, 5.8
     */
    public function test_owner_can_delete_own_article(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create(['thumbnail' => 'articles/test.jpg']);

        $this->actingAs($owner)
            ->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('articles.mine'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    /**
     * Test non-owner author cannot delete others' articles.
     *
     * Validates Requirement 5.8
     */
    public function test_non_owner_author_cannot_delete_others_article(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $otherAuthor = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create();

        $this->actingAs($otherAuthor)
            ->delete(route('articles.destroy', $article->id))
            ->assertStatus(403);

        $this->assertDatabaseHas('articles', ['id' => $article->id]);
    }

    /**
     * Test moderator can delete any article.
     *
     * Validates Requirement 5.8
     */
    public function test_moderator_can_delete_any_article(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $owner = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create();

        $this->actingAs($moderator)
            ->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('articles.mine'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    /**
     * Test admin can delete any article.
     *
     * Validates Requirement 5.8
     */
    public function test_admin_can_delete_any_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create();

        $this->actingAs($admin)
            ->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('articles.mine'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    /**
     * Test related comments are cascade deleted.
     *
     * Validates Requirement 5.7
     */
    public function test_deleting_article_cascade_deletes_comments(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $commenter = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create();

        // Create multiple comments on the article
        $comment1 = Comment::factory()
            ->for($article)
            ->for($commenter)
            ->create();
        $comment2 = Comment::factory()
            ->for($article)
            ->for($commenter)
            ->create();

        $this->assertDatabaseHas('comments', ['id' => $comment1->id]);
        $this->assertDatabaseHas('comments', ['id' => $comment2->id]);

        // Delete the article
        $this->actingAs($owner)
            ->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('articles.mine'));

        // Verify article and all comments are deleted
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment1->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment2->id]);
    }

    /**
     * Test category relationships are cleaned up.
     *
     * Validates Requirement 5.7
     */
    public function test_deleting_article_detaches_categories(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create();

        // Create and attach categories
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $article->categories()->attach([$category1->id, $category2->id]);

        $this->assertDatabaseHas('article_category', [
            'article_id' => $article->id,
            'category_id' => $category1->id,
        ]);

        // Delete the article
        $this->actingAs($owner)
            ->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('articles.mine'));

        // Verify relationships are detached
        $this->assertDatabaseMissing('article_category', [
            'article_id' => $article->id,
            'category_id' => $category1->id,
        ]);
        $this->assertDatabaseMissing('article_category', [
            'article_id' => $article->id,
            'category_id' => $category2->id,
        ]);

        // Verify categories still exist
        $this->assertDatabaseHas('categories', ['id' => $category1->id]);
        $this->assertDatabaseHas('categories', ['id' => $category2->id]);
    }

    /**
     * Test tag relationships are cleaned up.
     *
     * Validates Requirement 5.7
     */
    public function test_deleting_article_detaches_tags(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create();

        // Create and attach tags
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $article->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertDatabaseHas('article_tag', [
            'article_id' => $article->id,
            'tag_id' => $tag1->id,
        ]);

        // Delete the article
        $this->actingAs($owner)
            ->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('articles.mine'));

        // Verify relationships are detached
        $this->assertDatabaseMissing('article_tag', [
            'article_id' => $article->id,
            'tag_id' => $tag1->id,
        ]);
        $this->assertDatabaseMissing('article_tag', [
            'article_id' => $article->id,
            'tag_id' => $tag2->id,
        ]);

        // Verify tags still exist
        $this->assertDatabaseHas('tags', ['id' => $tag1->id]);
        $this->assertDatabaseHas('tags', ['id' => $tag2->id]);
    }

    /**
     * Test thumbnail file is deleted when article is deleted.
     *
     * Validates Requirement 5.7, 18.2
     */
    public function test_deleting_article_deletes_thumbnail_file(): void
    {
        Storage::disk('public')->put('articles/test-thumbnail.jpg', 'fake image content');

        $owner = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create(['thumbnail' => 'articles/test-thumbnail.jpg']);

        Storage::disk('public')->assertExists('articles/test-thumbnail.jpg');

        // Delete the article
        $this->actingAs($owner)
            ->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('articles.mine'));

        // Verify file is deleted
        Storage::disk('public')->assertMissing('articles/test-thumbnail.jpg');
    }

    /**
     * Test deleting article without thumbnail doesn't cause error.
     *
     * Validates Requirement 5.7
     */
    public function test_deleting_article_without_thumbnail(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create(['thumbnail' => null]);

        $this->actingAs($owner)
            ->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('articles.mine'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    /**
     * Test unauthenticated user cannot delete article.
     *
     * Validates Requirement 2.7
     */
    public function test_unauthenticated_user_cannot_delete_article(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create();

        $this->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('articles', ['id' => $article->id]);
    }

    /**
     * Test deletion returns success message.
     *
     * Validates Requirement 5.7
     */
    public function test_deletion_returns_success_message(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create();

        $this->actingAs($owner)
            ->delete(route('articles.destroy', $article->id))
            ->assertSessionHas('success', 'Artikel berhasil dihapus.');
    }

    /**
     * Test complex deletion scenario: article with comments, categories, tags, and thumbnail.
     *
     * Validates Requirement 5.7, 5.8
     */
    public function test_delete_article_with_all_relationships_and_file(): void
    {
        Storage::disk('public')->put('articles/complex-test.jpg', 'fake image content');

        $owner = User::factory()->create(['role' => 'author']);
        $commenter = User::factory()->create(['role' => 'author']);
        $article = Article::factory()
            ->for($owner)
            ->create(['thumbnail' => 'articles/complex-test.jpg']);

        // Add categories and tags
        $categories = Category::factory(2)->create();
        $tags = Tag::factory(3)->create();
        $article->categories()->attach($categories->pluck('id'));
        $article->tags()->attach($tags->pluck('id'));

        // Add comments
        $comments = Comment::factory(3)
            ->for($article)
            ->for($commenter)
            ->create();

        // Verify all relationships exist
        $this->assertCount(2, $article->categories);
        $this->assertCount(3, $article->tags);
        $this->assertCount(3, $article->comments);
        Storage::disk('public')->assertExists('articles/complex-test.jpg');

        // Delete article
        $this->actingAs($owner)
            ->delete(route('articles.destroy', $article->id))
            ->assertRedirect(route('articles.mine'));

        // Verify article and all relationships are deleted
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        foreach ($comments as $comment) {
            $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        }
        $this->assertDatabaseMissing('article_category', ['article_id' => $article->id]);
        $this->assertDatabaseMissing('article_tag', ['article_id' => $article->id]);

        // Verify categories and tags still exist
        foreach ($categories as $category) {
            $this->assertDatabaseHas('categories', ['id' => $category->id]);
        }
        foreach ($tags as $tag) {
            $this->assertDatabaseHas('tags', ['id' => $tag->id]);
        }

        // Verify file is deleted
        Storage::disk('public')->assertMissing('articles/complex-test.jpg');
    }
}
