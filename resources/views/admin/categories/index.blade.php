<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Manajemen Kategori — {{ config('app.name', 'Portal Berita') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Manajemen Kategori</h1>
                    <p class="mt-1 text-sm text-gray-500">Kelola semua kategori artikel</p>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="text-blue-600 hover:text-blue-800">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">

        {{-- Flash messages --}}
        @if (session('success'))
            <div role="alert" class="mb-6 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                <strong class="font-medium">Berhasil!</strong> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="mb-6 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                <strong class="font-medium">Kesalahan!</strong> {{ session('error') }}
            </div>
        @endif

        {{-- Add Category Form --}}
        <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Tambah Kategori Baru</h2>

            <form method="POST" action="{{ route('admin.categories.store') }}" novalidate>
                @csrf

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kategori <span class="text-red-600">*</span>
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            maxlength="255"
                            required
                            value="{{ old('name') }}"
                            placeholder="Contoh: Teknologi"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                            aria-describedby="@error('name') name-error @enderror"
                            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        >
                        @error('name')
                            <p id="name-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi (Opsional)
                        </label>
                        <input
                            id="description"
                            name="description"
                            type="text"
                            maxlength="500"
                            value="{{ old('description') }}"
                            placeholder="Deskripsi singkat kategori"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('description') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                            aria-describedby="@error('description') description-error @enderror"
                            aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                        >
                        @error('description')
                            <p id="description-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit button --}}
                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Tambah Kategori
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Categories Table --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Nama</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Slug</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Artikel</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Artikel Dipublikasikan</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($categories as $category)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-900 font-medium">
                                    {{ $category->name }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-mono text-xs">
                                    {{ $category->slug }}
                                </td>
                                <td class="px-6 py-4 text-center text-gray-600">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $category->total_articles_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-600">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $category->published_articles_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <button
                                        onclick="editCategory({{ $category->id }}, {{ json_encode($category->name) }}, {{ json_encode($category->description) }})"
                                        class="text-blue-600 hover:text-blue-800 font-medium transition"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onclick="deleteCategory({{ $category->id }}, {{ json_encode($category->name) }})"
                                        class="text-red-600 hover:text-red-800 font-medium transition"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada kategori. Tambahkan kategori baru di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($categories->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Edit Kategori</h3>
            </div>

            <form id="editForm" method="POST" novalidate class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="editName" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Kategori <span class="text-red-600">*</span>
                    </label>
                    <input
                        id="editName"
                        name="name"
                        type="text"
                        maxlength="255"
                        required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        aria-invalid="false"
                    >
                    <p id="editNameError" class="mt-1 text-xs text-red-600 hidden" role="alert"></p>
                </div>

                <div>
                    <label for="editDescription" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi (Opsional)
                    </label>
                    <input
                        id="editDescription"
                        name="description"
                        type="text"
                        maxlength="500"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <p id="editDescriptionError" class="mt-1 text-xs text-red-600 hidden" role="alert"></p>
                </div>

                <div class="flex gap-3 justify-end">
                    <button
                        type="button"
                        onclick="closeEditModal()"
                        class="px-4 py-2 text-gray-700 font-medium rounded-md border border-gray-300 hover:bg-gray-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Penghapusan</h3>
            </div>

            <div class="p-6">
                <p class="text-gray-700 mb-4">
                    Apakah Anda yakin ingin menghapus kategori <strong id="deleteCategoryName"></strong>?
                </p>
                <p class="text-sm text-gray-500">
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex gap-3 justify-end">
                <button
                    type="button"
                    onclick="closeDeleteModal()"
                    class="px-4 py-2 text-gray-700 font-medium rounded-md border border-gray-300 hover:bg-gray-50 transition"
                >
                    Batal
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 transition"
                    >
                        Hapus Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editCategory(id, name, description) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editForm');
            const nameInput = document.getElementById('editName');
            const descriptionInput = document.getElementById('editDescription');

            nameInput.value = name;
            descriptionInput.value = description || '';

            form.action = `/admin/categories/${id}`;
            modal.classList.remove('hidden');
            nameInput.focus();
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteCategory(id, name) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const nameSpan = document.getElementById('deleteCategoryName');

            nameSpan.textContent = name;
            form.action = `/admin/categories/${id}`;
            modal.classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.getElementById('editModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'editModal') closeEditModal();
        });

        document.getElementById('deleteModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'deleteModal') closeDeleteModal();
        });

        // Close modals on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeEditModal();
                closeDeleteModal();
            }
        });
    </script>

</body>
</html>
