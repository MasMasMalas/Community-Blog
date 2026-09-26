<?php

use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Public routes — no authentication required
// ---------------------------------------------------------------------------

// Home / article feed
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');

// Article detail (read-only)
Route::get('/articles', [\App\Http\Controllers\ArticleController::class, 'index'])
    ->name('articles.index');
Route::get('/articles/{slug}', [\App\Http\Controllers\ArticleController::class, 'show'])
    ->name('articles.show');

// Category listing & articles per category
Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])
    ->name('categories.index');
Route::get('/categories/{slug}', [\App\Http\Controllers\CategoryController::class, 'show'])
    ->name('categories.show');

// Tag — articles per tag
Route::get('/tags/{slug}', [\App\Http\Controllers\TagController::class, 'show'])
    ->name('tags.show');

// Full-text search
Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])
    ->name('search');

// ---------------------------------------------------------------------------
// Guest-only routes (unauthenticated users only)
// ---------------------------------------------------------------------------

Route::middleware('guest')->group(function () {
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'create'])
        ->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store']);

    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showForm'])
        ->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
});

// ---------------------------------------------------------------------------
// Authenticated routes — any logged-in, active user
// ---------------------------------------------------------------------------

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])
        ->name('logout');

    // User profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])
        ->name('profile.show');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])
        ->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])
        ->name('profile.password');
    Route::post('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'uploadAvatar'])
        ->name('profile.avatar');

    // Dashboard — all authenticated users land here; controller redirects per role
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    // -----------------------------------------------------------------------
    // Author routes — authors and admins can create / manage articles
    // -----------------------------------------------------------------------
    Route::middleware('check.role:author,moderator,admin')->group(function () {
        Route::get('/articles/create', [\App\Http\Controllers\ArticleController::class, 'create'])
            ->name('articles.create');
        Route::post('/articles', [\App\Http\Controllers\ArticleController::class, 'store'])
            ->name('articles.store');
        Route::get('/articles/{id}/edit', [\App\Http\Controllers\ArticleController::class, 'edit'])
            ->name('articles.edit');
        Route::put('/articles/{id}', [\App\Http\Controllers\ArticleController::class, 'update'])
            ->name('articles.update');
        Route::delete('/articles/{id}', [\App\Http\Controllers\ArticleController::class, 'destroy'])
            ->name('articles.destroy');
        Route::post('/articles/{id}/submit', [\App\Http\Controllers\ArticleController::class, 'submit'])
            ->name('articles.submit');

        // Author's own article list
        Route::get('/my-articles', [\App\Http\Controllers\ArticleController::class, 'myArticles'])
            ->name('articles.mine');
    });

    // -----------------------------------------------------------------------
    // Comment routes — any authenticated user
    // -----------------------------------------------------------------------
    Route::post('/articles/{slug}/comments', [\App\Http\Controllers\CommentController::class, 'store'])
        ->name('comments.store');
    Route::delete('/comments/{id}', [\App\Http\Controllers\CommentController::class, 'destroy'])
        ->name('comments.destroy');

    // -----------------------------------------------------------------------
    // Admin panel — Moderator + Admin
    // -----------------------------------------------------------------------
    Route::middleware('check.role:moderator,admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
                ->name('dashboard');

            // Article moderation queue
            Route::get('/articles', [\App\Http\Controllers\Admin\ArticleModerationController::class, 'index'])
                ->name('articles.index');
            Route::put('/articles/{id}/approve', [\App\Http\Controllers\Admin\ArticleModerationController::class, 'approve'])
                ->name('articles.approve');
            Route::put('/articles/{id}/reject', [\App\Http\Controllers\Admin\ArticleModerationController::class, 'reject'])
                ->name('articles.reject');
            Route::put('/articles/{id}/revision', [\App\Http\Controllers\Admin\ArticleModerationController::class, 'revision'])
                ->name('articles.revision');

            // Comment moderation queue
            Route::get('/comments', [\App\Http\Controllers\Admin\CommentModerationController::class, 'index'])
                ->name('comments.index');
            Route::put('/comments/{id}/approve', [\App\Http\Controllers\Admin\CommentModerationController::class, 'approve'])
                ->name('comments.approve');
            Route::delete('/comments/{id}', [\App\Http\Controllers\Admin\CommentModerationController::class, 'destroy'])
                ->name('comments.destroy');

            // ---------------------------------------------------------------
            // Admin-only sub-group
            // ---------------------------------------------------------------
            Route::middleware('check.role:admin')->group(function () {

                // Category management
                Route::get('/categories', [\App\Http\Controllers\Admin\CategoryManagementController::class, 'index'])
                    ->name('categories.index');
                Route::post('/categories', [\App\Http\Controllers\Admin\CategoryManagementController::class, 'store'])
                    ->name('categories.store');
                Route::put('/categories/{id}', [\App\Http\Controllers\Admin\CategoryManagementController::class, 'update'])
                    ->name('categories.update');
                Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\CategoryManagementController::class, 'destroy'])
                    ->name('categories.destroy');

                // User management
                Route::get('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])
                    ->name('users.index');
                Route::put('/users/{id}/role', [\App\Http\Controllers\Admin\UserManagementController::class, 'updateRole'])
                    ->name('users.role');
                Route::put('/users/{id}/status', [\App\Http\Controllers\Admin\UserManagementController::class, 'updateStatus'])
                    ->name('users.status');
            });
        });
});
