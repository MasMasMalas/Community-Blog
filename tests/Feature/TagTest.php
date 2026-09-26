<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test viewing the public tags index page.
     */
    public function test_can_view_tags_index(): void
    {
        $tag1 = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        $tag2 = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);

        // Create published articles for each tag
        $author = User::factory()->create();
        $article1 = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::PUBLISHED,
                'published_at' => now(),
            ]);
        $article2 = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::PUBLISHED,
                'published_at' => now(),
            ]);

        $tag1->articles()->attach($article1->id);
        $tag2->articles()->attach($article2->id);

        $response = $this->get('/tags');

        $response->assertStatus(200);
        $response->assertSee($tag1->name);
        $response->assertSee($tag2->name);
    }

    /**
     * Test viewing articles for a specific tag.
     */
    public function test_can_view_articles_by_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        $author = User::factory()->create();

        // Create published and draft articles
        $publishedArticle = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::PUBLISHED,
                'title' => 'Published Article',
                'published_at' => now(),
            ]);
        $draftArticle = Article::factory()
            ->for($author)
            ->create(['status' => Article::DRAFT, 'title' => 'Draft Article']);

        $tag->articles()->attach([$publishedArticle->id, $draftArticle->id]);

        $response = $this->get(route('tags.show', $tag->slug));

        $response->assertStatus(200);
        $response->assertSee($publishedArticle->title);
        $response->assertDontSee($draftArticle->title);
    }

    /**
     * Test that only published articles are shown when viewing tag.
     */
    public function test_tag_page_only_shows_published_articles(): void
    {
        $tag = Tag::factory()->create();
        $author = User::factory()->create();

        $published = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::PUBLISHED,
                'published_at' => now(),
            ]);
        $pending = Article::factory()
            ->for($author)
            ->create(['status' => Article::PENDING_REVIEW]);
        $draft = Article::factory()
            ->for($author)
            ->create(['status' => Article::DRAFT]);

        $tag->articles()->attach([$published->id, $pending->id, $draft->id]);

        $response = $this->get(route('tags.show', $tag->slug));

        $response->assertStatus(200);
        $response->assertSee($published->title);
        $response->assertDontSee($pending->title);
        $response->assertDontSee($draft->title);
    }

    /**
     * Test that 404 is returned for non-existent tag.
     */
    public function test_non_existent_tag_returns_404(): void
    {
        $response = $this->get(route('tags.show', 'non-existent-tag'));

        $response->assertStatus(404);
    }

    /**
     * Test slug generation on tag creation.
     */
    public function test_slug_generated_automatically_on_create(): void
    {
        $tag = Tag::create([
            'name' => 'Laravel Framework',
        ]);

        $this->assertEquals('laravel-framework', $tag->slug);
    }

    /**
     * Test slug is unique and persists.
     */
    public function test_slug_is_unique_and_persists(): void
    {
        $tag1 = Tag::create(['name' => 'PHP Framework']);
        $tag2 = Tag::create(['name' => 'Symfony Framework']);

        $this->assertNotEquals($tag1->slug, $tag2->slug);
        $this->assertEquals('php-framework', $tag1->slug);
        $this->assertEquals('symfony-framework', $tag2->slug);
    }

    /**
     * Test slug regeneration on tag update.
     */
    public function test_slug_updated_when_name_changes(): void
    {
        $tag = Tag::create(['name' => 'Laravel']);
        $this->assertEquals('laravel', $tag->slug);

        $tag->update(['name' => 'Symfony']);
        $this->assertEquals('symfony', $tag->slug);
    }

    /**
     * Test tag can be associated with multiple articles.
     */
    public function test_tag_can_have_multiple_articles(): void
    {
        $tag = Tag::factory()->create();
        $author = User::factory()->create();

        $articles = Article::factory(3)
            ->for($author)
            ->create(['status' => Article::PUBLISHED]);

        $tag->articles()->attach($articles->pluck('id'));

        $this->assertCount(3, $tag->articles);
    }

    /**
     * Test article can have multiple tags.
     */
    public function test_article_can_have_multiple_tags(): void
    {
        $article = Article::factory()->create(['status' => Article::PUBLISHED]);
        $tags = Tag::factory(3)->create();

        $article->tags()->attach($tags->pluck('id'));

        $this->assertCount(3, $article->tags);
    }

    /**
     * Test published articles count scope.
     */
    public function test_published_articles_count(): void
    {
        $tag = Tag::factory()->create();
        $author = User::factory()->create();

        $published = Article::factory(2)
            ->for($author)
            ->create([
                'status' => Article::PUBLISHED,
                'published_at' => now(),
            ]);
        $draft = Article::factory(1)
            ->for($author)
            ->create(['status' => Article::DRAFT]);

        $allArticleIds = collect([$published, [$draft]])->flatten()->pluck('id')->toArray();
        $tag->articles()->attach($allArticleIds);

        $tagWithCount = Tag::withPublishedCount()->find($tag->id);

        $this->assertEquals(2, $tagWithCount->published_articles_count);
    }

    /**
     * Test that tag articles are ordered by published date descending.
     */
    public function test_tag_articles_ordered_by_published_date(): void
    {
        $tag = Tag::factory()->create();
        $author = User::factory()->create();

        $article1 = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::PUBLISHED,
                'published_at' => now()->subDays(5),
                'title' => 'Old Article',
            ]);
        $article2 = Article::factory()
            ->for($author)
            ->create([
                'status' => Article::PUBLISHED,
                'published_at' => now(),
                'title' => 'New Article',
            ]);

        $tag->articles()->attach([$article1->id, $article2->id]);

        $response = $this->get(route('tags.show', $tag->slug));

        $response->assertSeeInOrder(['New Article', 'Old Article']);
    }

    /**
     * Test pagination on tag articles.
     */
    public function test_tag_articles_paginated(): void
    {
        $tag = Tag::factory()->create();
        $author = User::factory()->create();

        $articles = Article::factory(25)
            ->for($author)
            ->create([
                'status' => Article::PUBLISHED,
                'published_at' => now(),
            ]);

        $tag->articles()->attach($articles->pluck('id'));

        $response = $this->get(route('tags.show', $tag->slug));

        $response->assertStatus(200);
        // Verify pagination link exists
        $response->assertSee('Next');
    }

    /**
     * Test tag with no articles displays empty message.
     */
    public function test_tag_with_no_articles_shows_empty_message(): void
    {
        $tag = Tag::factory()->create(['name' => 'Empty Tag', 'slug' => 'empty-tag']);

        $response = $this->get(route('tags.show', $tag->slug));

        $response->assertStatus(200);
        $response->assertSee('Belum ada artikel dengan tag ini');
    }
}
