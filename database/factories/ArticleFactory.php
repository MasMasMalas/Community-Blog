<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => str($title)->slug(),
            'excerpt' => fake()->paragraph(2),
            'content' => fake()->paragraphs(5, asText: true),
            'thumbnail' => null,
            'status' => Article::DRAFT,
            'views' => 0,
        ];
    }

    /**
     * Indicate that the article should be published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Article::PUBLISHED,
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the article is pending review.
     */
    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Article::PENDING_REVIEW,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Indicate that the article is in revision.
     */
    public function revision(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Article::REVISION,
        ]);
    }

    /**
     * Indicate that the article was rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Article::REJECTED,
            'rejection_note' => fake()->paragraph(),
        ]);
    }
}
