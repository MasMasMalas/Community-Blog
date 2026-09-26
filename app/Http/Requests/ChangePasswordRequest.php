<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) auth()->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Persyaratan:
     *   3.7  – verifikasi kata sandi lama sebelum menyimpan yang baru
     *   3.8  – validasi kekuatan kata sandi baru (sama seperti registrasi)
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! Hash::check($value, auth()->user()->password)) {
                        $fail('Kata sandi saat ini tidak sesuai.');
                    }
                },
            ],
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
            // current_password
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',

            // password
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.mixed' => 'Kata sandi harus mengandung huruf besar dan huruf kecil.',
            'password.numbers' => 'Kata sandi harus mengandung minimal satu angka.',
        ];
    }
}
