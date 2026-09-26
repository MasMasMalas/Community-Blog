<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'avatar',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => 'string',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    /**
     * Articles authored by this user.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Comments posted by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // -------------------------------------------------------------------------
    // Helper / Role Methods
    // -------------------------------------------------------------------------

    /**
     * Get the full URL to the user's avatar.
     * Returns a default avatar URL if no avatar is set.
     *
     * Persyaratan: 18.1, 18.2, 18.6, 18.7
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::disk('public')->url($this->avatar);
        }

        // Return a default placeholder avatar
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=random';
    }

    /**
     * Determine whether the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Determine whether the user is a moderator.
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    /**
     * Determine whether the user is an author.
     */
    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    /**
     * Determine whether the user account is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
