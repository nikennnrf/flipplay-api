<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi untuk PATCH /api/media/{media}.
 * Memakai "sometimes" agar field yang tidak dikirim tidak dianggap error.
 */
class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ignore(): judul milik media ini sendiri tidak dihitung sebagai duplikat
            'title' => ['sometimes', 'required', 'string', 'max:150', Rule::unique('media', 'title')->ignore($this->route('media'))],
            'type' => ['sometimes', 'required', Rule::in(Media::TYPES)],
            'genres' => ['sometimes', 'required', 'array', 'min:1'],
            'genres.*' => ['required', 'string', 'max:30', 'distinct'],
            'year' => ['sometimes', 'required', 'integer', 'between:1950,' . (now()->year + 1)],
            'score' => ['sometimes', 'nullable', 'numeric', 'between:0,10'],
            'episodes' => ['sometimes', 'integer', 'min:1'],
            'duration' => ['sometimes', 'required', 'integer', 'min:1', 'max:600'],
            'synopsis' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return (new StoreMediaRequest())->messages();
    }
}
