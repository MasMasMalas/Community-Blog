@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Manajemen Tag</h1>
        <button
            onclick="document.getElementById('addTagModal').classList.remove('hidden')"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            + Tambah Tag
        </button>
    </div>

    <!-- Success/Error Messages -->
    @if ($message = Session::get('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $message }}
        </div>
    @endif

    <!-- Tags Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Nama Tag</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Slug</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-600">Artikel Published</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-600">Total Artikel</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tags as $tag)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $tag->name }}</div>
                            @if ($tag->description)
                                <div class="text-sm text-gray-500">{{ Str::limit($tag->description, 100) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $tag->slug }}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">
                            {{ $tag->published_articles_count }}
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">
                            {{ $tag->total_articles_count }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button
                                onclick="editTag({{ json_encode($tag) }})"
                                class="text-blue-600 hover:text-blue-900 text-sm mr-3">
                                Edit
                            </button>
                            <form
                                action="{{ route('admin.tags.destroy', $tag->id) }}"
                                method="POST"
                                class="inline"
                                onsubmit="return confirm('Yakin ingin menghapus tag ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Belum ada tag. <button
                                onclick="document.getElementById('addTagModal').classList.remove('hidden')"
                                class="text-blue-600 hover:text-blue-900">Tambah tag sekarang</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($tags->hasPages())
        <div class="mt-6">
            {{ $tags->links() }}
        </div>
    @endif
</div>

<!-- Add/Edit Tag Modal -->
<div id="addTagModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Tambah Tag Baru</h2>

        <form id="tagForm" action="{{ route('admin.tags.store') }}" method="POST">
            @csrf
            <input type="hidden" id="formMethod" name="_method" value="POST">

            <!-- Name Input -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Tag <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    id="tagName"
                    name="name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Contoh: Laravel"
                    required>
                <span id="nameError" class="text-red-500 text-sm hidden"></span>
            </div>

            <!-- Description Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
                <textarea
                    id="tagDescription"
                    name="description"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Deskripsi singkat tentang tag ini..."></textarea>
                <span id="descriptionError" class="text-red-500 text-sm hidden"></span>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button
                    type="button"
                    onclick="closeTagModal()"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Batal
                </button>
                <button
                    type="submit"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editTag(tag) {
        document.getElementById('modalTitle').textContent = 'Edit Tag';
        document.getElementById('tagForm').action = `{{ route('admin.tags.update', '') }}/${tag.id}`;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('tagName').value = tag.name;
        document.getElementById('tagDescription').value = tag.description || '';
        document.getElementById('addTagModal').classList.remove('hidden');
    }

    function closeTagModal() {
        document.getElementById('addTagModal').classList.add('hidden');
        document.getElementById('tagForm').reset();
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('tagForm').action = '{{ route('admin.tags.store') }}';
        document.getElementById('modalTitle').textContent = 'Tambah Tag Baru';
        document.getElementById('nameError').classList.add('hidden');
        document.getElementById('descriptionError').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('addTagModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeTagModal();
        }
    });
</script>
@endsection
