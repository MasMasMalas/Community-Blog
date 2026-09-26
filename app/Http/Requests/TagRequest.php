<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is checked in the route middleware (check.role:admin)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tagId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tags', 'name')
                    ->when($tagId, function ($query) use ($tagId) {
                        return $query->ignore($tagId);
                    }),
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
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
            'name.required' => 'Nama tag harus diisi.',
            'name.string' => 'Nama tag harus berupa teks.',
            'name.max' => 'Nama tag maksimal 100 karakter.',
            'name.unique' => 'Nama tag sudah digunakan. Gunakan nama yang berbeda.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ];
    }
}
