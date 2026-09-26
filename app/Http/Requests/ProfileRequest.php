<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9_]+$/',
                'max:255',
                "unique:users,username,{$userId}",
            ],
            'email' => [
                'required',
                'email',
                "unique:users,email,{$userId}",
            ],
            'bio' => ['nullable', 'string', 'max:300'],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,gif',
                'max:2048', // 2 MB in kilobytes
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'username.required' => 'Username harus diisi.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, dan underscore.',
            'username.max' => 'Username tidak boleh lebih dari 255 karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Email harus berformat valid.',
            'email.unique' => 'Email sudah digunakan.',
            'bio.max' => 'Bio tidak boleh lebih dari 300 karakter.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus JPEG, PNG, atau GIF.',
            'avatar.max' => 'Ukuran gambar tidak boleh lebih dari 2 MB.',
        ];
    }
}
