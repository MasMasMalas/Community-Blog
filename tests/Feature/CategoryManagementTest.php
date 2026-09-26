<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $author;

    protected User $moderator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->author = User::factory()->create(['role' => 'author']);
        $this->moderator = User::factory()->create(['role' => 'moderator']);
    }

    // =========================================================================
    // Authorization Tests
    // =========================================================================

    public function test_only_admin_can_access_category_management(): void
    {
        // Guest cannot access
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('login'));

        // Author cannot access
        $this->actingAs($this->author)
            ->get(route('admin.categories.index'))
            ->assertForbidden();

        // Moderator cannot access
        $this->actingAs($this->moderator)
            ->get(route('admin.categories.index'))
            ->assertForbidden();

        // Admin can access
        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertOk();
    }

    // =========================================================================
    // Index Tests
    // =========================================================================

    public function test_admin_can_view_categories_index(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertViewHas('categories');
    }

    public function test_categories_are_paginated(): void
    {
        Category::factory()->count(25)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.index'));

        $response->assertOk();
        $this->assertCount(20, $response->viewData('categories'));
    }

    public function test_index_includes_article_counts(): void
    {
        $category = Category::factory()->create();
        $article1 = Article::factory()->state(['status' => 'published'])->create();
        $article2 = Article::factory()->state(['status' => 'draft'])->create();

        $category->articles()->attach([$article1->id, $article2->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.index'));

        $categories = $response->viewData('categories');
        $this->assertEquals(2, $categories[0]->total_articles_count);
        $this->assertEquals(1, $categories[0]->published_articles_count);
    }

    // =========================================================================
    // Store Tests
    // =========================================================================

    public function test_admin_can_create_category(): void
    {
        $data = [
            'name' => 'Teknologi Baru',
            'description' => 'Berita tentang teknologi terkini',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $data);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Teknologi Baru',
            'description' => 'Berita tentang teknologi terkini',
        ]);
    }

    public function test_category_slug_is_generated_automatically(): void
    {
        $data = ['name' => 'Teknologi Baru'];

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $data);

        $category = Category::where('name', 'Teknologi Baru')->first();

        $this->assertNotNull($category->slug);
        $this->assertEquals('teknologi-baru', $category->slug);
    }

    public function test_slug_is_unique_with_numeric_suffix(): void
    {
        // Create first category with slug 'teknologi'
        $cat1 = Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);

        // Now create second category that would generate the same base slug 'teknologi'
        // by modifying the slug generation method to produce a collision
        $data = ['name' => 'TEKNOLOGI'];  // Different case, same base slug

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $data);

        $response->assertRedirect();

        // The new category should have gotten a suffixed slug due to collision
        $cat2 = Category::where('name', 'TEKNOLOGI')->first();

        // Verify that the slugs are different
        $this->assertEquals('teknologi', $cat1->slug);
        $this->assertNotEquals($cat1->slug, $cat2->slug);
        $this->assertStringContainsString('teknologi', $cat2->slug);
    }

    public function test_category_name_is_unique(): void
    {
        Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Teknologi']);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), []);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_name_max_length_is_enforced(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_description_is_optional(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Kategori Tanpa Deskripsi',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Kategori Tanpa Deskripsi',
            'description' => null,
        ]);
    }

    public function test_category_description_max_length_is_enforced(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Valid Category',
                'description' => str_repeat('a', 501),
            ]);

        $response->assertSessionHasErrors('description');
    }

    // =========================================================================
    // Update Tests
    // =========================================================================

    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Lama']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Baru',
                'description' => 'Deskripsi baru',
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Baru',
            'description' => 'Deskripsi baru',
        ]);
    }

    public function test_update_regenerates_slug_if_name_changed(): void
    {
        $category = Category::factory()->create(['name' => 'Teknologi', 'slug' => 'teknologi']);

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Sains',
            ]);

        $category->refresh();

        $this->assertEquals('sains', $category->slug);
    }

    public function test_update_preserves_slug_if_name_unchanged(): void
    {
        $category = Category::factory()->create(['name' => 'Teknologi', 'slug' => 'teknologi']);

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Teknologi',
            ]);

        $category->refresh();

        $this->assertEquals('teknologi', $category->slug);
    }

    public function test_update_validates_unique_name_excluding_itself(): void
    {
        $category1 = Category::factory()->create(['name' => 'Teknologi']);
        $category2 = Category::factory()->create(['name' => 'Sains']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category2), [
                'name' => 'Teknologi',
            ]);

        $response->assertSessionHasErrors('name');
    }

    // =========================================================================
    // Destroy Tests
    // =========================================================================

    public function test_admin_can_delete_category_without_articles(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertModelMissing($category);
    }

    public function test_admin_cannot_delete_category_with_articles(): void
    {
        $category = Category::factory()->create();
        $article = Article::factory()->create();
        $category->articles()->attach($article->id);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertModelExists($category);
    }

    public function test_delete_error_message_includes_article_count_and_titles(): void
    {
        $category = Category::factory()->create();
        $article1 = Article::factory()->create(['title' => 'Article One']);
        $article2 = Article::factory()->create(['title' => 'Article Two']);
        $article3 = Article::factory()->create(['title' => 'Article Three']);
        $category->articles()->attach([$article1->id, $article2->id, $article3->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $errorMessage = $response->getSession()->get('error');

        // Verify article count is in the message
        $this->assertStringContainsString('3', $errorMessage);

        // Verify article titles are in the message
        $this->assertStringContainsString('Article One', $errorMessage);
        $this->assertStringContainsString('Article Two', $errorMessage);
        $this->assertStringContainsString('Article Three', $errorMessage);
    }

    // =========================================================================
    // Slug Generation Unit Tests
    // =========================================================================

    public function test_generate_unique_slug_creates_base_slug(): void
    {
        $slug = Category::generateUniqueSlug('Teknologi Baru');

        $this->assertEquals('teknologi-baru', $slug);
    }

    public function test_generate_unique_slug_adds_suffix_on_collision(): void
    {
        Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);

        $slug = Category::generateUniqueSlug('Teknologi');

        $this->assertEquals('teknologi-2', $slug);
    }

    public function test_generate_unique_slug_increments_suffix_for_multiple_collisions(): void
    {
        Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);
        Category::create(['name' => 'Teknologi 2', 'slug' => 'teknologi-2']);

        $slug = Category::generateUniqueSlug('Teknologi');

        $this->assertEquals('teknologi-3', $slug);
    }

    public function test_generate_unique_slug_excludes_specified_category(): void
    {
        $category = Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);

        $slug = Category::generateUniqueSlug('Teknologi', $category->id);

        $this->assertEquals('teknologi', $slug);
    }

    public function test_generate_unique_slug_handles_special_characters(): void
    {
        $slug = Category::generateUniqueSlug('Teknologi & Inovasi!!!');

        $this->assertEquals('teknologi-inovasi', $slug);
    }

    public function test_generate_unique_slug_handles_whitespace(): void
    {
        $slug = Category::generateUniqueSlug('  Teknologi   Baru  ');

        $this->assertEquals('teknologi-baru', $slug);
    }

    public function test_generate_unique_slug_converts_to_lowercase(): void
    {
        $slug = Category::generateUniqueSlug('TEKNOLOGI BARU');

        $this->assertEquals('teknologi-baru', $slug);
    }
}
