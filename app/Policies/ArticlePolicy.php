<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Determine whether the user can view any articles.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the article.
     */
    public function view(User $user, Article $article): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create articles.
     */
    public function create(User $user): bool
    {
        return $user->isAuthor() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the article.
     */
    public function update(User $user, Article $article): bool
    {
        // Owner can always edit
        if ($user->id === $article->user_id) {
            return true;
        }

        // Moderator or admin can edit any article
        if ($user->isModerator() || $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the article.
     *
     * Requirement 5.7, 5.8 — Only owner, moderator, or admin can delete.
     */
    public function delete(User $user, Article $article): bool
    {
        // Owner can always delete
        if ($user->id === $article->user_id) {
            return true;
        }

        // Moderator or admin can delete any article
        if ($user->isModerator() || $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the article.
     */
    public function restore(User $user, Article $article): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the article.
     */
    public function forceDelete(User $user, Article $article): bool
    {
        return false;
    }
}
