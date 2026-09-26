<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    /**
     * The articles that belong to this category.
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_category');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Eager-load a count of published articles for each category.
     *
     * Usage: Category::withPublishedCount()->get()
     */
    public function scopeWithPublishedCount(Builder $query): Builder
    {
        return $query->withCount([
            'articles as published_articles_count' => function (Builder $q) {
                $q->where('status', Article::PUBLISHED);
            },
        ]);
    }

    // ─── Helper Methods ──────────────────────────────────────────────────────

    /**
     * Generate a unique slug from a category name.
     * If the slug already exists, append a numeric suffix.
     *
     * @param  ?int  $excludeId  The category ID to exclude from the uniqueness check (for updates)
     */
    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = str($name)
            ->lower()
            ->slug();

        $query = self::where('slug', 'like', $baseSlug.'%');

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        $count = $query->count();

        if ($count === 0) {
            return $baseSlug;
        }

        return $baseSlug.'-'.($count + 1);
    }
}
