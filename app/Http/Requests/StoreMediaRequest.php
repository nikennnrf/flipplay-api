<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi untuk POST /api/media.
 */
class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150', 'unique:media,title'],
            'type' => ['required', Rule::in(Media::TYPES)],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['required', 'string', 'max:30', 'distinct'],
            'year' => ['required', 'integer', 'between:1950,' . (now()->year + 1)],
            'score' => ['nullable', 'numeric', 'between:0,10'],
            'episodes' => ['sometimes', 'integer', 'min:1'],
            'duration' => ['required', 'integer', 'min:1', 'max:600'],
            'synopsis' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul media wajib diisi.',
            'title.unique' => 'Judul media sudah ada di FlipPlay.',
            'title.max' => 'Judul media maksimal 150 karakter.',
            'type.required' => 'Jenis media wajib diisi.',
            'type.in' => 'Jenis media harus TV atau Movie.',
            'genres.required' => 'Minimal satu genre wajib diisi.',
            'genres.array' => 'Genre harus berupa array, contoh: ["Action", "Drama"].',
            'genres.*.distinct' => 'Genre tidak boleh sama.',
            'year.required' => 'Tahun rilis wajib diisi.',
            'year.between' => 'Tahun rilis harus di antara :min dan :max.',
            'score.between' => 'Skor harus di antara 0 dan 10.',
            'episodes.min' => 'Jumlah episode minimal 1.',
            'duration.required' => 'Durasi (menit) wajib diisi.',
            'duration.min' => 'Durasi minimal 1 menit.',
        ];
    }
}
