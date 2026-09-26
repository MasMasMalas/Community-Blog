<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the categories table with initial data.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Teknologi', 'description' => 'Berita dan tips seputar teknologi terkini'],
            ['name' => 'Bisnis', 'description' => 'Informasi tentang bisnis dan ekonomi'],
            ['name' => 'Pendidikan', 'description' => 'Tips dan berita seputar dunia pendidikan'],
            ['name' => 'Kesehatan', 'description' => 'Informasi kesehatan dan wellness'],
            ['name' => 'Lifestyle', 'description' => 'Gaya hidup dan tips sehari-hari'],
            ['name' => 'Olahraga', 'description' => 'Berita dan info seputar olahraga'],
            ['name' => 'Traveling', 'description' => 'Tips dan cerita tentang perjalanan'],
            ['name' => 'Kuliner', 'description' => 'Resep dan ulasan restoran'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => $this->generateUniqueSlug($category['name']),
                'description' => $category['description'] ?? null,
            ]);
        }
    }

    /**
     * Generate a unique slug for a category name.
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = str($name)
            ->lower()
            ->slug();

        $count = Category::where('slug', 'like', $slug.'%')
            ->count();

        if ($count === 0) {
            return $slug;
        }

        return $slug.'-'.($count + 1);
    }
}
