<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * CRUD standar untuk entitas Media.
 */
class MediaController extends Controller
{
    /**
     * GET /api/media
     * Query opsional: ?search=frieren&type=TV&genre=Drama&per_page=10
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 10), 1), 50);

        $media = Media::query()
            ->search($request->query('search'))
            ->ofType($request->query('type'))
            ->hasGenre($request->query('genre'))
            ->withCount('users')
            ->orderBy('title')
            ->paginate($perPage)
            ->withQueryString();

        return MediaResource::collection($media);
    }

    /**
     * POST /api/media
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = Media::create($request->validated());

        return (new MediaResource($media))
            ->additional(['message' => 'Media berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/media/{media}
     * Detail media beserta pengguna yang memasukkannya ke koleksi.
     */
    public function show(Media $media): MediaResource
    {
        $media->load('users')->loadCount('users');

        return new MediaResource($media);
    }

    /**
     * PATCH /api/media/{media}
     */
    public function update(UpdateMediaRequest $request, Media $media): MediaResource
    {
        $media->update($request->validated());

        return (new MediaResource($media->fresh()))
            ->additional(['message' => 'Media berhasil diperbarui.']);
    }

    /**
     * DELETE /api/media/{media}
     * Baris pivot ikut terhapus karena foreign key memakai cascadeOnDelete().
     */
    public function destroy(Media $media): JsonResponse
    {
        $title = $media->title;
        $media->delete();

        return response()->json([
            'message' => "Media '{$title}' berhasil dihapus.",
        ]);
    }
}
