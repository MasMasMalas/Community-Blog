<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->paragraph(),
            'status' => 'pending',
        ];
    }

    /**
     * Create an approved comment.
     */
    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }
}
