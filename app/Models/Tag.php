<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    // ─── Lifecycle Hooks ─────────────────────────────────────────────────────

    /**
     * Boot the model.
     * Generate slug automatically when creating or updating a tag.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $tag) {
            if (! $tag->slug) {
                $tag->slug = self::generateUniqueSlug($tag->name);
            }
        });

        static::updating(function (self $tag) {
            // Regenerate slug if name changed
            if ($tag->isDirty('name')) {
                $tag->slug = self::generateUniqueSlug($tag->name, $tag->id);
            }
        });
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    /**
     * The articles that are tagged with this tag.
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tag');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Eager-load a count of published articles for each tag.
     *
     * Usage: Tag::withPublishedCount()->get()
     */
    public function scopeWithPublishedCount(Builder $query): Builder
    {
        return $query->withCount([
            'articles as published_articles_count' => function (Builder $q) {
                $q->where('status', Article::PUBLISHED);
            },
        ]);
    }

    /**
     * Eager-load a count of total articles for each tag (all statuses).
     *
     * Usage: Tag::withTotalCount()->get()
     */
    public function scopeWithTotalCount(Builder $query): Builder
    {
        return $query->withCount('articles as total_articles_count');
    }

    // ─── Helper Methods ──────────────────────────────────────────────────────

    /**
     * Generate a unique slug from a tag name.
     * If the slug already exists, append a numeric suffix.
     *
     * @param  ?int  $excludeId  The tag ID to exclude from the uniqueness check (for updates)
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
