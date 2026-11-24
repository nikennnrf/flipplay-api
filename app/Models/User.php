<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Pengguna FlipPlay. Lanjutan class User dari Modul 3:
 * koleksi media sekarang disimpan di pivot table media_user.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const PLAN_LIMITS = [
        'Free' => 5,
        'Premium' => 100,
    ];

    protected $fillable = [
        'name',
        'email',
        'plan',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Koleksi media milik pengguna (many-to-many). */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_user')
            ->withPivot('is_watched')
            ->withTimestamps();
    }

    /** Batas jumlah media di koleksi sesuai paket. */
    public function collectionLimit(): int
    {
        return self::PLAN_LIMITS[$this->plan] ?? self::PLAN_LIMITS['Free'];
    }

    public function hasInCollection(int $mediaId): bool
    {
        return $this->media()->wherePivot('media_id', $mediaId)->exists();
    }
}
