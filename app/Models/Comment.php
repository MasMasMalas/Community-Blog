<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    // ─── Status constants ─────────────────────────────────────────────────────

    const PENDING = 'pending';

    const APPROVED = 'approved';

    // ─── Mass assignment ──────────────────────────────────────────────────────

    protected $fillable = [
        'article_id',
        'user_id',
        'parent_id',
        'content',
        'status',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    /**
     * The article this comment belongs to.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * The user who wrote this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The parent comment (for nested/reply threads).
     * Returns null for top-level comments.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * The direct replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Filter to approved comments only.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::APPROVED);
    }

    /**
     * Filter to pending comments only.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::PENDING);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Whether the comment has been approved by a moderator.
     */
    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    /**
     * Whether the comment is still awaiting moderation.
     */
    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
