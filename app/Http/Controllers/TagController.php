<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Display a listing of all tags with published article count.
     * Used to show the public tags directory.
     */
    public function index(): View
    {
        $tags = Tag::withPublishedCount()
            ->orderBy('name')
            ->paginate(20);

        return view('tags.index', compact('tags'));
    }

    /**
     * Display articles for a specific tag.
     *
     * Shows all published articles tagged with the specified tag,
     * paginated with 20 articles per page.
     */
    public function show(string $slug): View
    {
        // Find tag by slug, fail if not found
        $tag = Tag::where('slug', $slug)->firstOrFail();

        // Get published articles for this tag, paginated
        $articles = $tag->articles()
            ->where('status', Article::PUBLISHED)
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        return view('tags.show', [
            'tag' => $tag,
            'articles' => $articles,
        ]);
    }
}
