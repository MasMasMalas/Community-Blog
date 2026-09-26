@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Buat Artikel Baru</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Tulis dan publikasikan ide Anda ke komunitas</p>
        </div>

        <!-- Form -->
        <form action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Judul Artikel <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="{{ old('title') }}"
                    placeholder="Masukkan judul artikel yang menarik"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('title') border-red-500 @enderror"
                    required
                >
                @error('title')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Isi Artikel <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="content" 
                    name="content" 
                    rows="12"
                    placeholder="Tulis isi artikel Anda di sini. Minimal 100 karakter."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white font-mono text-sm @error('content') border-red-500 @enderror"
                    required
                >{{ old('content') }}</textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span id="char-count">0</span> / 100 karakter minimum
                </p>
                @error('content')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Categories -->
            <div>
                <label for="categories" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-800 max-h-40 overflow-y-auto @error('categories') border-red-500 @enderror">
                    @if($categories->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Tidak ada kategori tersedia. Hubungi admin.</p>
                    @else
                        <div class="space-y-2">
                            @foreach($categories as $category)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        name="categories[]" 
                                        value="{{ $category->id }}"
                                        @checked(in_array($category->id, old('categories', [])))
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                                    >
                                    <span class="ml-3 text-gray-700 dark:text-gray-300">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pilih minimal satu kategori</p>
                @error('categories')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tags -->
            <div>
                <label for="tags" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Tag (Opsional)
                </label>
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-800 max-h-40 overflow-y-auto">
                    @if($tags->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Tidak ada tag tersedia.</p>
                    @else
                        <div class="space-y-2">
                            @foreach($tags as $tag)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        name="tags[]" 
                                        value="{{ $tag->id }}"
                                        @checked(in_array($tag->id, old('tags', [])))
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                                    >
                                    <span class="ml-3 text-gray-700 dark:text-gray-300">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                @error('tags')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Thumbnail -->
            <div>
                <label for="thumbnail" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Gambar Sampul (Opsional)
                </label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-blue-500 transition-colors cursor-pointer" id="thumbnail-drop-zone">
                    <input 
                        type="file" 
                        id="thumbnail" 
                        name="thumbnail" 
                        accept="image/jpeg,image/png,image/gif"
                        class="hidden"
                    >
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-6-6h6m0 0v6m0-6L28 20M12 36l8-8 8 8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700">Pilih file</span> atau seret ke sini
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">JPEG, PNG, GIF. Maksimal 5 MB</p>
                </div>
                <div id="thumbnail-preview" class="mt-4"></div>
                @error('thumbnail')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex gap-3 pt-6">
                <button 
                    type="submit" 
                    class="flex-1 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                >
                    Simpan sebagai Draft
                </button>
                <a 
                    href="{{ route('dashboard') }}" 
                    class="flex-1 px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold rounded-lg transition-colors text-center"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Character counter script -->
<script>
    const contentTextarea = document.getElementById('content');
    const charCount = document.getElementById('char-count');

    function updateCharCount() {
        charCount.textContent = contentTextarea.value.length;
    }

    contentTextarea.addEventListener('input', updateCharCount);
    updateCharCount();

    // Thumbnail preview and drag-drop
    const dropZone = document.getElementById('thumbnail-drop-zone');
    const fileInput = document.getElementById('thumbnail');
    const preview = document.getElementById('thumbnail-preview');

    dropZone.addEventListener('click', () => fileInput.click());

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900');
        }, false);
    });

    dropZone.addEventListener('drop', handleDrop, false);
    fileInput.addEventListener('change', handleFileSelect);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        handleFileSelect({ target: { files } });
    }

    function handleFileSelect(e) {
        const files = e.target.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    preview.innerHTML = `
                        <div class="relative w-32 h-32 mx-auto">
                            <img src="${event.target.result}" alt="Preview" class="w-full h-full object-cover rounded-lg">
                            <button type="button" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700" onclick="resetThumbnail()">×</button>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        }
    }

    function resetThumbnail() {
        fileInput.value = '';
        preview.innerHTML = '';
    }
</script>
@endsection
