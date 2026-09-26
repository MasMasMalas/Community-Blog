<?php

use App\Http\Controllers\Admin\ArticleModerationController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\CommentModerationController;
use App\Http\Controllers\Admin\TagManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Public routes — no authentication required
// ---------------------------------------------------------------------------

// Home / article feed
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

// Article detail (read-only)
Route::get('/articles', [ArticleController::class, 'index'])
    ->name('articles.index');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])
    ->name('articles.show');

// Category listing & articles per category
Route::get('/categories', [CategoryController::class, 'index'])
    ->name('categories.index');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])
    ->name('categories.show');

// Tag — articles per tag
Route::get('/tags', [TagController::class, 'index'])
    ->name('tags.index');
Route::get('/tags/{slug}', [TagController::class, 'show'])
    ->name('tags.show');

// Full-text search
Route::get('/search', [SearchController::class, 'index'])
    ->name('search');

// ---------------------------------------------------------------------------
// Guest-only routes (unauthenticated users only)
// ---------------------------------------------------------------------------

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])
        ->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'showForm'])
        ->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// ---------------------------------------------------------------------------
// Authenticated routes — any logged-in, active user
// ---------------------------------------------------------------------------

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    // User profile
    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'changePassword'])
        ->name('profile.password.form');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])
        ->name('profile.avatar');

    // Dashboard — all authenticated users land here; controller redirects per role
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // -----------------------------------------------------------------------
    // Author routes — authors and admins can create / manage articles
    // -----------------------------------------------------------------------
    Route::middleware('check.role:author,moderator,admin')->group(function () {
        Route::get('/articles/create', [ArticleController::class, 'create'])
            ->name('articles.create');
        Route::post('/articles', [ArticleController::class, 'store'])
            ->name('articles.store');
        Route::get('/articles/{id}/edit', [ArticleController::class, 'edit'])
            ->name('articles.edit');
        Route::put('/articles/{id}', [ArticleController::class, 'update'])
            ->name('articles.update');
        Route::delete('/articles/{id}', [ArticleController::class, 'destroy'])
            ->name('articles.destroy');
        Route::post('/articles/{id}/submit', [ArticleController::class, 'submit'])
            ->name('articles.submit');

        // Author's own article list
        Route::get('/my-articles', [ArticleController::class, 'myArticles'])
            ->name('articles.mine');
    });

    // -----------------------------------------------------------------------
    // Comment routes — any authenticated user
    // -----------------------------------------------------------------------
    Route::post('/articles/{slug}/comments', [CommentController::class, 'store'])
        ->name('comments.store');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    // -----------------------------------------------------------------------
    // Admin panel — Moderator + Admin
    // -----------------------------------------------------------------------
    Route::middleware('check.role:moderator,admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
                ->name('dashboard');

            // Article moderation queue
            Route::get('/articles', [ArticleModerationController::class, 'index'])
                ->name('articles.index');
            Route::put('/articles/{id}/approve', [ArticleModerationController::class, 'approve'])
                ->name('articles.approve');
            Route::put('/articles/{id}/reject', [ArticleModerationController::class, 'reject'])
                ->name('articles.reject');
            Route::put('/articles/{id}/revision', [ArticleModerationController::class, 'revision'])
                ->name('articles.revision');

            // Comment moderation queue
            Route::get('/comments', [CommentModerationController::class, 'index'])
                ->name('comments.index');
            Route::put('/comments/{id}/approve', [CommentModerationController::class, 'approve'])
                ->name('comments.approve');
            Route::delete('/comments/{id}', [CommentModerationController::class, 'destroy'])
                ->name('comments.destroy');

            // ---------------------------------------------------------------
            // Admin-only sub-group
            // ---------------------------------------------------------------
            Route::middleware('check.role:admin')->group(function () {

                // Category management
                Route::get('/categories', [CategoryManagementController::class, 'index'])
                    ->name('categories.index');
                Route::post('/categories', [CategoryManagementController::class, 'store'])
                    ->name('categories.store');
                Route::put('/categories/{id}', [CategoryManagementController::class, 'update'])
                    ->name('categories.update');
                Route::delete('/categories/{id}', [CategoryManagementController::class, 'destroy'])
                    ->name('categories.destroy');

                // Tag management
                Route::get('/tags', [TagManagementController::class, 'index'])
                    ->name('tags.index');
                Route::post('/tags', [TagManagementController::class, 'store'])
                    ->name('tags.store');
                Route::put('/tags/{id}', [TagManagementController::class, 'update'])
                    ->name('tags.update');
                Route::delete('/tags/{id}', [TagManagementController::class, 'destroy'])
                    ->name('tags.destroy');

                // User management
                Route::get('/users', [UserManagementController::class, 'index'])
                    ->name('users.index');
                Route::put('/users/{id}/role', [UserManagementController::class, 'updateRole'])
                    ->name('users.role');
                Route::put('/users/{id}/status', [UserManagementController::class, 'updateStatus'])
                    ->name('users.status');
            });
        });
});
