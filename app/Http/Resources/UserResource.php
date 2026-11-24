<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Format JSON untuk data pengguna beserta koleksinya.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'plan' => $this->plan,
            'collection_limit' => $this->collectionLimit(),
            'collection_count' => $this->whenCounted('media'),

            // Muncul hanya jika relasi media di-load
            'collection' => MediaResource::collection($this->whenLoaded('media')),
            'remaining_watch_minutes' => $this->whenLoaded('media', fn () => $this->media
                ->reject(fn ($media) => (bool) $media->pivot->is_watched)
                ->sum(fn ($media) => $media->total_watch_minutes)),

            // Muncul saat user ditampilkan dari sisi media (detail media -> collected_by)
            'collection_info' => $this->whenPivotLoaded('media_user', fn () => [
                'is_watched' => (bool) $this->pivot->is_watched,
                'added_at' => $this->pivot->created_at,
            ]),
        ];
    }
}
