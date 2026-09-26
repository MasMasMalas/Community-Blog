<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryManagementController extends Controller
{
    /**
     * Display a listing of categories with counts of related articles.
     */
    public function index(): View
    {
        $categories = Category::withPublishedCount()
            ->withCount('articles as total_articles_count')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Generate unique slug
        $validated['slug'] = Category::generateUniqueSlug($validated['name']);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(CategoryRequest $request, int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $validated = $request->validated();

        // Regenerate slug if name changed
        if ($validated['name'] !== $category->name) {
            $validated['slug'] = Category::generateUniqueSlug($validated['name'], $category->id);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category from storage.
     * Shows a warning if the category has related articles, including article titles.
     */
    public function destroy(int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        // Check if category has related articles
        $articles = $category->articles()->select('id', 'title')->get();
        $articleCount = $articles->count();

        if ($articleCount > 0) {
            // Build error message with article count and titles
            $articleTitles = $articles->pluck('title')->join(', ');
            $errorMessage = "Kategori ini masih memiliki $articleCount artikel: $articleTitles. ";
            $errorMessage .= 'Hapus atau pindahkan artikel terlebih dahulu.';

            return redirect()->route('admin.categories.index')
                ->with('error', $errorMessage);
        }

        $categoryName = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori \"$categoryName\" berhasil dihapus.");
    }
}
