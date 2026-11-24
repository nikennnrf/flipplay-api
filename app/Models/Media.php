<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Lanjutan class Media dari Modul 3. Sekarang menjadi Model Eloquent
 * yang tersimpan di tabel `media`. Anime/Movie dibedakan lewat kolom `type`.
 */
class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    public const TYPES = ['TV', 'Movie'];

    // "media" tidak punya bentuk jamak di Inggris, jadi nama tabel ditulis eksplisit
    protected $table = 'media';

    protected $fillable = [
        'title',
        'type',
        'genres',
        'year',
        'score',
        'episodes',
        'duration',
        'synopsis',
    ];

    // Nilai default saat object dibuat (Movie cukup 1 episode)
    protected $attributes = [
        'episodes' => 1,
    ];

    protected function casts(): array
    {
        return [
            'genres' => 'array',   // JSON di database <-> array di PHP
            'year' => 'integer',
            'score' => 'float',
            'episodes' => 'integer',
            'duration' => 'integer',
        ];
    }

    // ===== Relasi =====

    /** Pengguna yang memasukkan media ini ke koleksinya. */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'media_user')
            ->withPivot('is_watched')
            ->withTimestamps();
    }

    // ===== Accessor & Mutator =====

    /** Mutator: rapikan spasi pada judul sebelum disimpan. */
    protected function title(): Attribute
    {
        return Attribute::set(fn (string $value) => trim(preg_replace('/\s+/', ' ', $value)));
    }

    /** Accessor: total menit menonton ($media->total_watch_minutes). */
    protected function totalWatchMinutes(): Attribute
    {
        return Attribute::get(fn () => $this->duration * $this->episodes);
    }

    // ===== Query Scope =====

    /** Media::search('frieren') -> cari berdasarkan judul. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where('title', 'like', '%' . trim($term) . '%'));
    }

    /** Media::ofType('Movie') */
    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when($type, fn (Builder $q) => $q->where('type', $type));
    }

    /** Media::hasGenre('Comedy') -> genre disimpan sebagai JSON array. */
    public function scopeHasGenre(Builder $query, ?string $genre): Builder
    {
        return $query->when($genre, fn (Builder $q) => $q->where('genres', 'like', '%"' . trim($genre) . '"%'));
    }
}
