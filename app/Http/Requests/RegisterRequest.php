<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Anyone on the guest route may register.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Persyaratan:
     *   1.1  – kolom: name, username, email, password, password_confirmation
     *   1.2  – email unik
     *   1.3  – username unik
     *   1.4  – username: hanya [a-zA-Z0-9_], min 3, max 20 karakter
     *   1.5  – password = konfirmasi
     *   1.6  – password min 8 karakter, 1 huruf besar, 1 huruf kecil, 1 angka
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:users,username',
            ],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers(),
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
            // name
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max'      => 'Nama lengkap tidak boleh melebihi 255 karakter.',

            // username
            'username.required' => 'Username wajib diisi.',
            'username.min'      => 'Username minimal 3 karakter.',
            'username.max'      => 'Username tidak boleh melebihi 20 karakter.',
            'username.regex'    => 'Username hanya boleh mengandung huruf, angka, dan garis bawah (_).',
            'username.unique'   => 'Username sudah digunakan. Silakan pilih username lain.',

            // email
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
            'email.max'      => 'Alamat email tidak boleh melebihi 255 karakter.',
            'email.unique'   => 'Alamat email sudah terdaftar. Silakan gunakan email lain atau masuk.',

            // password
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.mixed'     => 'Kata sandi harus mengandung huruf besar dan huruf kecil.',
            'password.numbers'   => 'Kata sandi harus mengandung minimal satu angka.',
        ];
    }
}
