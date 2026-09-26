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
}
