<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled in the controller with policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'filled'], // filled ensures not just whitespace
            'content' => ['required', 'string', 'min:100'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.filled' => 'Judul tidak boleh kosong.',
            'content.required' => 'Isi artikel wajib diisi.',
            'content.min' => 'Isi artikel minimal harus 100 karakter.',
        ];
    }
}
