<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagManagementController extends Controller
{
    /**
     * Display a listing of tags with counts of related articles.
     */
    public function index(): View
    {
        $tags = Tag::withPublishedCount()
            ->withCount('articles as total_articles_count')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.tags.index', [
            'tags' => $tags,
        ]);
    }

    /**
     * Store a newly created tag in storage.
     */
    public function store(TagRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Generate unique slug
        $validated['slug'] = Tag::generateUniqueSlug($validated['name']);

        Tag::create($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag berhasil ditambahkan.');
    }

    /**
     * Update the specified tag in storage.
     */
    public function update(TagRequest $request, int $id): RedirectResponse
    {
        $tag = Tag::findOrFail($id);
        $validated = $request->validated();

        // Regenerate slug if name changed
        if ($validated['name'] !== $tag->name) {
            $validated['slug'] = Tag::generateUniqueSlug($validated['name'], $tag->id);
        }

        $tag->update($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag berhasil diperbarui.');
    }

    /**
     * Remove the specified tag from storage.
     * Shows a warning if the tag has related articles.
     */
    public function destroy(int $id): RedirectResponse
    {
        $tag = Tag::findOrFail($id);

        // Check if tag has related articles
        $articleCount = $tag->articles()->count();

        if ($articleCount > 0) {
            return redirect()->route('admin.tags.index')
                ->with('error', "Tag ini masih memiliki $articleCount artikel. Hapus atau ubah tag artikel terlebih dahulu.");
        }

        $tagName = $tag->name;
        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', "Tag \"$tagName\" berhasil dihapus.");
    }
}
