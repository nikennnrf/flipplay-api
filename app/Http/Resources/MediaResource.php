<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Format JSON tunggal untuk data Media.
 */
class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'genres' => $this->genres,
            'year' => $this->year,
            'score' => $this->score,
            'episodes' => $this->episodes,
            'duration_minutes' => $this->duration,
            'total_watch_minutes' => $this->total_watch_minutes,   // dari accessor
            'synopsis' => $this->synopsis,

            // Hanya muncul jika dihitung dengan withCount('users')
            'collected_count' => $this->whenCounted('users'),

            // Hanya muncul jika relasi users di-load (with/load)
            'collected_by' => UserResource::collection($this->whenLoaded('users')),

            // Hanya muncul saat media diambil lewat koleksi pengguna (pivot media_user)
            'collection' => $this->whenPivotLoaded('media_user', fn () => [
                'is_watched' => (bool) $this->pivot->is_watched,
                'added_at' => $this->pivot->created_at,
            ]),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
