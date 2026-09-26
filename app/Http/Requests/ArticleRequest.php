<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Only authenticated users with author/moderator/admin role can create articles.
     * This is already protected by the route middleware, but we double-check here.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAuthor();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Persyaratan:
     *   4.2  – editor untuk judul dan isi
     *   4.3  – satu atau lebih kategori
     *   4.4  – tag dapat ditambahkan
     *   4.5  – upload thumbnail
     *   4.6  – slug otomatis dari judul
     *   4.10 – reject data tidak valid dengan error message
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Requirement 4.2 – Title and content fields
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'excerpt' => [
                'nullable',
                'string',
                'max:500',
            ],
            'content' => [
                'required',
                'string',
                'min:100',
            ],

            // Requirement 4.3 – One or more categories required
            'categories' => [
                'required',
                'array',
                'min:1',
            ],
            'categories.*' => [
                'integer',
                'exists:categories,id',
            ],

            // Requirement 4.4 – Tags (optional but validated if provided)
            'tags' => [
                'nullable',
                'array',
            ],
            'tags.*' => [
                'integer',
                'exists:tags,id',
            ],

            // Requirement 4.5 – Thumbnail upload (optional)
            // MIME types: JPEG, PNG, GIF; Max 5MB (5120 KB)
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpeg,png,gif',
                'max:5120',
            ],
        ];
    }

    /**
     * Custom validation messages (Bahasa Indonesia).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Title
            'title.required' => 'Judul artikel wajib diisi.',
            'title.max' => 'Judul tidak boleh melebihi 255 karakter.',

            // Excerpt
            'excerpt.max' => 'Ringkasan tidak boleh melebihi 500 karakter.',

            // Content
            'content.required' => 'Isi artikel wajib diisi.',
            'content.min' => 'Isi artikel minimal harus 100 karakter.',

            // Categories
            'categories.required' => 'Pilih minimal satu kategori untuk artikel.',
            'categories.array' => 'Format kategori tidak valid.',
            'categories.min' => 'Pilih minimal satu kategori untuk artikel.',
            'categories.*.exists' => 'Salah satu kategori yang dipilih tidak valid.',

            // Tags
            'tags.array' => 'Format tag tidak valid.',
            'tags.*.exists' => 'Salah satu tag yang dipilih tidak valid.',

            // Thumbnail
            'thumbnail.image' => 'File thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Thumbnail harus berupa file JPEG, PNG, atau GIF.',
            'thumbnail.max' => 'Ukuran thumbnail tidak boleh melebihi 5 MB.',
        ];
    }
}
