<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * GET /api/users
     */
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(User::withCount('media')->orderBy('id')->get());
    }

    /**
     * GET /api/users/{user}
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user->load('media')->loadCount('media'));
    }
}
