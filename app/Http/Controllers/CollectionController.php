<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCollectionRequest;
use App\Http\Requests\UpdateCollectionItemRequest;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Custom action di luar CRUD standar: mengubah relasi (pivot media_user)
 * antara pengguna dan media.
 */
class CollectionController extends Controller
{
    /**
     * GET /api/users/{user}/collection
     */
    public function index(User $user): UserResource
    {
        return new UserResource($user->load('media')->loadCount('media'));
    }

    /**
     * POST /api/users/{user}/collection   body: { "media_id": 1 }
     *
     * Pola "baca kondisi -> validasi -> ubah": cek duplikat & batas paket, lalu attach().
     * Dibungkus DB::transaction() + lockForUpdate() agar dua request bersamaan
     * untuk user yang sama tidak sama-sama lolos pengecekan (race condition).
     */
    public function store(AddToCollectionRequest $request, User $user): JsonResponse
    {
        $mediaId = (int) $request->validated('media_id');

        return DB::transaction(function () use ($user, $mediaId) {
            // Kunci baris user; request lain untuk user ini menunggu sampai transaksi selesai
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($user->hasInCollection($mediaId)) {
                return response()->json([
                    'message' => 'Media sudah ada di koleksi pengguna ini.',
                ], 409);
            }

            if ($user->media()->count() >= $user->collectionLimit()) {
                return response()->json([
                    'message' => "Koleksi penuh. Paket {$user->plan} maksimal {$user->collectionLimit()} media.",
                ], 409);
            }

            $user->media()->attach($mediaId, ['is_watched' => false]);

            return (new UserResource($user->load('media')->loadCount('media')))
                ->additional(['message' => 'Media berhasil ditambahkan ke koleksi.'])
                ->response()
                ->setStatusCode(201);
        });
    }

    /**
     * PATCH /api/users/{user}/collection/{media}   body: { "is_watched": true }
     */
    public function update(UpdateCollectionItemRequest $request, User $user, Media $media): JsonResponse
    {
        if (! $user->hasInCollection($media->id)) {
            return $this->notInCollection($media);
        }

        $user->media()->updateExistingPivot($media->id, [
            'is_watched' => $request->boolean('is_watched'),
        ]);

        return (new UserResource($user->load('media')->loadCount('media')))
            ->additional(['message' => 'Status tontonan berhasil diperbarui.'])
            ->response();
    }

    /**
     * DELETE /api/users/{user}/collection/{media}
     */
    public function destroy(User $user, Media $media): JsonResponse
    {
        if (! $user->hasInCollection($media->id)) {
            return $this->notInCollection($media);
        }

        $user->media()->detach($media->id);

        return response()->json([
            'message' => "Media '{$media->title}' berhasil dihapus dari koleksi {$user->name}.",
        ]);
    }

    private function notInCollection(Media $media): JsonResponse
    {
        return response()->json([
            'message' => "Media '{$media->title}' tidak ada di koleksi pengguna ini.",
        ], 404);
    }
}
