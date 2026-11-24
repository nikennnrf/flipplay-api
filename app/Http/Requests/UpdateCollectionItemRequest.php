<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi untuk PATCH /api/users/{user}/collection/{media}.
 */
class UpdateCollectionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_watched' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_watched.required' => 'is_watched wajib diisi.',
            'is_watched.boolean' => 'is_watched harus bernilai true atau false.',
        ];
    }
}
