<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use HasFactory;

    // Status constants
    const DRAFT = 'draft';

    const PENDING_REVIEW = 'pending_review';

    const PUBLISHED = 'published';

    const REVISION = 'revision';

    const REJECTED = 'rejected';

    const ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'thumbnail',
        'status',
        'rejection_note',
        'views',
        'submitted_at',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'views' => 'integer',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
            'status' => 'string',
        ];
    }

    // =========================================================================
    // Relations
    // =========================================================================

    /**
     * The user (author) who wrote this article.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The categories this article belongs to.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }

    /**
     * The tags attached to this article.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    /**
     * The comments posted on this article.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Scope to filter only published articles.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::PUBLISHED);
    }

    /**
     * Scope to filter articles awaiting review.
     */
    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', self::PENDING_REVIEW);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Get the full URL for the article thumbnail.
     * Returns a placeholder if no thumbnail is set.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            return Storage::disk('public')->url($this->thumbnail);
        }

        return 'https://via.placeholder.com/800x600?text=No+Thumbnail';
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Check whether the article is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::PUBLISHED;
    }

    /**
     * Check whether the article is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    /**
     * Check whether the article is pending review.
     */
    public function isPendingReview(): bool
    {
        return $this->status === self::PENDING_REVIEW;
    }

    /**
     * Check whether the article is in revision.
     */
    public function isRevision(): bool
    {
        return $this->status === self::REVISION;
    }

    /**
     * Check whether the article has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::REJECTED;
    }

    /**
     * Generate a unique slug from an article title.
     * If the slug already exists, append a numeric suffix.
     *
     * Requirement 4.6, 4.7
     *
     * @param  ?int  $excludeId  The article ID to exclude from the uniqueness check (for updates)
     */
    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = str($title)
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
