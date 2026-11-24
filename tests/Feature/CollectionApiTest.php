<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionApiTest extends TestCase
{
    use RefreshDatabase;

    private User $ega;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->ega = User::where('email', 'ega@flipplay.test')->first();
    }

    private function mediaId(string $title): int
    {
        return Media::where('title', $title)->value('id');
    }

    public function test_menampilkan_koleksi_pengguna(): void
    {
        $this->getJson("/api/users/{$this->ega->id}/collection")
            ->assertOk()
            ->assertJsonPath('data.name', 'Ega')
            ->assertJsonPath('data.collection_count', 3)
            ->assertJsonCount(3, 'data.collection')
            ->assertJsonStructure(['data' => ['collection' => [['id', 'title', 'collection' => ['is_watched', 'added_at']]]]]);
    }

    public function test_menambahkan_media_ke_koleksi(): void
    {
        $this->postJson("/api/users/{$this->ega->id}/collection", ['media_id' => $this->mediaId('Death Note')])
            ->assertCreated()
            ->assertJsonPath('data.collection_count', 4)
            ->assertJsonPath('message', 'Media berhasil ditambahkan ke koleksi.');

        $this->assertDatabaseHas('media_user', [
            'user_id' => $this->ega->id,
            'media_id' => $this->mediaId('Death Note'),
            'is_watched' => false,
        ]);
    }

    public function test_menambahkan_media_yang_sama_dua_kali_ditolak_409(): void
    {
        $url = "/api/users/{$this->ega->id}/collection";
        $body = ['media_id' => $this->mediaId('Death Note')];

        $this->postJson($url, $body)->assertCreated();
        $this->postJson($url, $body)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Media sudah ada di koleksi pengguna ini.');

        $this->assertSame(1, $this->ega->media()->wherePivot('media_id', $body['media_id'])->count());
    }

    public function test_menambahkan_media_gagal_validasi(): void
    {
        $url = "/api/users/{$this->ega->id}/collection";

        $this->postJson($url, [])->assertStatus(422)->assertJsonValidationErrors(['media_id']);
        $this->postJson($url, ['media_id' => 999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['media_id' => 'Media dengan ID tersebut tidak ditemukan.']);
    }

    public function test_koleksi_paket_free_maksimal_5_media(): void
    {
        $url = "/api/users/{$this->ega->id}/collection";   // sudah berisi 3 media

        $this->postJson($url, ['media_id' => $this->mediaId('Death Note')])->assertCreated();
        $this->postJson($url, ['media_id' => $this->mediaId('Steins;Gate')])->assertCreated();
        $this->postJson($url, ['media_id' => $this->mediaId('Silver Spoon')])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Koleksi penuh. Paket Free maksimal 5 media.');
    }

    public function test_pengguna_404_jika_tidak_ada(): void
    {
        $this->getJson('/api/users/999/collection')
            ->assertNotFound()
            ->assertJsonPath('message', 'User dengan ID 999 tidak ditemukan.');
    }

    public function test_menandai_media_sudah_ditonton(): void
    {
        $frieren = $this->mediaId('Sousou no Frieren');

        $this->patchJson("/api/users/{$this->ega->id}/collection/{$frieren}", ['is_watched' => true])
            ->assertOk()
            ->assertJsonPath('message', 'Status tontonan berhasil diperbarui.');

        $this->assertDatabaseHas('media_user', ['user_id' => $this->ega->id, 'media_id' => $frieren, 'is_watched' => true]);
    }

    public function test_menghapus_media_dari_koleksi(): void
    {
        $frieren = $this->mediaId('Sousou no Frieren');
        $url = "/api/users/{$this->ega->id}/collection/{$frieren}";

        $this->deleteJson($url)->assertOk();
        $this->assertDatabaseMissing('media_user', ['user_id' => $this->ega->id, 'media_id' => $frieren]);

        // Menghapus lagi -> sudah tidak ada di koleksi
        $this->deleteJson($url)
            ->assertNotFound()
            ->assertJsonPath('message', "Media 'Sousou no Frieren' tidak ada di koleksi pengguna ini.");

        // Media-nya sendiri tetap ada (yang dihapus hanya relasinya)
        $this->assertDatabaseHas('media', ['id' => $frieren]);
    }

    public function test_daftar_dan_detail_pengguna(): void
    {
        $this->getJson('/api/users')->assertOk()->assertJsonCount(2, 'data');

        $this->getJson("/api/users/{$this->ega->id}")
            ->assertOk()
            ->assertJsonPath('data.plan', 'Free')
            ->assertJsonPath('data.collection_limit', 5);
    }
}
