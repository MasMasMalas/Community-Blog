<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Http\Requests\SubmitArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    // =========================================================================
    // Create Article Form
    // =========================================================================

    /**
     * Show the form for creating a new article.
     *
     * Accessible only to authenticated authors and above.
     * Requirement 4.1
     */
    public function create(): View
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('articles.create', compact('categories', 'tags'));
    }

    // =========================================================================
    // Store Article
    // =========================================================================

    /**
     * Store a newly created article in the database.
     *
     * - Validates the article request (ArticleRequest)
     * - Generates unique slug from title (Requirement 4.6, 4.7)
     * - Handles thumbnail upload if provided (Requirement 4.5)
     * - Creates article with status=DRAFT (Requirement 4.8)
     * - Attaches categories and tags (Requirement 4.3, 4.4)
     * - Saves: author, categories, title, slug, content, thumbnail, created_at, status (Requirement 4.9)
     * - Shows validation errors on invalid data (Requirement 4.10)
     */
    public function store(ArticleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Requirement 4.6, 4.7 – Generate unique slug from title
        $slug = Article::generateUniqueSlug($validated['title']);

        // Handle thumbnail upload if provided (Requirement 4.5, 18.1-18.7)
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            // Store in storage/app/public/articles with a UUID-based name
            $thumbnailPath = $file->storeAs(
                'articles',
                uniqid().'_'.$file->hashName(),
                'public'
            );
        }

        // Requirement 4.8 – Create article with status=DRAFT and authenticated user as author
        // Requirement 4.9 – Save author, categories, title, slug, content, thumbnail, created_at, status
        $article = Article::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'thumbnail' => $thumbnailPath,
            'status' => Article::DRAFT,
        ]);

        // Attach categories (Requirement 4.3)
        if (isset($validated['categories'])) {
            $article->categories()->attach($validated['categories']);
        }

        // Attach tags if provided (Requirement 4.4)
        if (isset($validated['tags']) && is_array($validated['tags']) && count($validated['tags']) > 0) {
            $article->tags()->attach($validated['tags']);
        }

        return redirect()->route('articles.show', $article->slug)
            ->with('success', 'Artikel berhasil disimpan sebagai draf.');
    }

    // =========================================================================
    // View Article
    // =========================================================================

    /**
     * Display the specified article by slug.
     */
    public function show(string $slug): View
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        return view('articles.show', compact('article'));
    }

    /**
     * Display a listing of articles.
     */
    public function index(): View
    {
        $articles = Article::published()->paginate(10);

        return view('articles.index', compact('articles'));
    }

    // =========================================================================
    // Edit Article
    // =========================================================================

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Article $article): View
    {
        // Requirement 5.8 – Check authorization
        $this->authorize('update', $article);

        $categories = Category::all();
        $tags = Tag::all();

        return view('articles.edit', compact('article', 'categories', 'tags'));
    }

    /**
     * Update the specified article in storage.
     */
    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        // Requirement 5.8 – Check authorization
        $this->authorize('update', $article);

        $validated = $request->validated();

        // Update slug if title changed
        if ($article->title !== $validated['title']) {
            $validated['slug'] = Article::generateUniqueSlug($validated['title'], $article->id);
        }

        // Handle thumbnail upload if provided
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if it exists
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }

            $file = $request->file('thumbnail');
            $validated['thumbnail'] = $file->storeAs(
                'articles',
                uniqid().'_'.$file->hashName(),
                'public'
            );
        }

        $article->update($validated);

        // Update categories
        if (isset($validated['categories'])) {
            $article->categories()->sync($validated['categories']);
        }

        // Update tags
        if (isset($validated['tags'])) {
            $article->tags()->sync($validated['tags']);
        } else {
            $article->tags()->sync([]);
        }

        return redirect()->route('articles.show', $article->slug)
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    // =========================================================================
    // Delete Article
    // =========================================================================

    /**
     * Delete an article.
     *
     * Authorize: only owner, moderator, or admin can delete.
     * Cascade delete: comments, category relationships, tag relationships.
     * Delete thumbnail file if it exists.
     *
     * Requirement 5.7, 5.8
     *
     * @return RedirectResponse
     */
    public function destroy(Article $article)
    {
        // Authorization: check if user has permission to delete this article
        $this->authorize('delete', $article);

        // Delete associated comments (cascade)
        $article->comments()->delete();

        // Detach categories (many-to-many relationship cleanup)
        $article->categories()->detach();

        // Detach tags (many-to-many relationship cleanup)
        $article->tags()->detach();

        // Delete thumbnail file if it exists
        if ($article->thumbnail) {
            Storage::disk('public')->delete($article->thumbnail);
        }

        // Delete the article itself
        $article->delete();

        return redirect()
            ->route('articles.mine')
            ->with('success', 'Artikel berhasil dihapus.');
    }

    // =========================================================================
    // Author's Article List
    // =========================================================================

    /**
     * Display articles belonging to the authenticated user.
     */
    public function myArticles(): View
    {
        $articles = auth()->user()->articles()->paginate(10);

        return view('articles.my-articles', compact('articles'));
    }

    // =========================================================================
    // Submit for Review
    // =========================================================================

    /**
     * Submit an article for moderation review.
     *
     * Validates title (not empty) and content (minimum 100 characters).
     * Changes status from DRAFT to PENDING_REVIEW after validation.
     * Records submission time.
     *
     * Requirement 6.1, 6.2, 6.3, 6.4, 6.5, 6.7
     *
     * @return RedirectResponse
     */
    public function submit(Article $article, SubmitArticleRequest $request): RedirectResponse
    {
        // Authorization: only owner, moderator, or admin can submit
        $this->authorize('update', $article);

        // Check that article is in draft or pending_review status (Requirement 6.5)
        if (! $article->isDraft() && ! $article->isPendingReview()) {
            return redirect()
                ->back()
                ->with('error', 'Hanya artikel berstatus draft atau menunggu tinjauan yang dapat dikirim.');
        }

        // Update status to pending review and record submission time (Requirement 6.4, 6.7)
        $article->update([
            'status' => Article::PENDING_REVIEW,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Artikel berhasil dikirim untuk ditinjau.');
    }
}
