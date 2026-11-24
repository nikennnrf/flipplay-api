<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
| Semua route di file ini otomatis berawalan /api (diatur di bootstrap/app.php).
| Cek daftar lengkap dengan: php artisan route:list --path=api
*/

// CRUD standar Media: index, store, show, update, destroy
// parameters(): nama parameter route dibuat {media} agar cocok dengan Route Model Binding Media $media
Route::apiResource('media', MediaController::class)->parameters(['media' => 'media']);

// Data pengguna (hanya baca)
Route::apiResource('users', UserController::class)->only(['index', 'show']);

// Custom action: koleksi media milik pengguna (mengubah pivot media_user)
Route::prefix('users/{user}/collection')
    ->controller(CollectionController::class)
    ->name('users.collection.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::patch('{media}', 'update')->name('update');
        Route::delete('{media}', 'destroy')->name('destroy');
    });
