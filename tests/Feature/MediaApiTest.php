<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function validPayload(array $override = []): array
    {
        return array_merge([
            'title' => 'Chainsaw Man',
            'type' => 'TV',
            'genres' => ['Action', 'Fantasy'],
            'year' => 2022,
            'score' => 8.5,
            'episodes' => 12,
            'duration' => 24,
        ], $override);
    }

    public function test_index_menampilkan_daftar_media_dengan_pagination(): void
    {
        $this->getJson('/api/media')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonStructure(['data' => [['id', 'title', 'type', 'genres', 'collected_count']], 'links', 'meta']);
    }

    public function test_index_bisa_mencari_berdasarkan_judul(): void
    {
        $this->getJson('/api/media?search=frieren')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Sousou no Frieren');

        $this->getJson('/api/media?search=naruto')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_index_bisa_filter_type_dan_genre(): void
    {
        $this->getJson('/api/media?type=Movie')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/media?genre=Comedy')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_store_berhasil_membuat_media_baru(): void
    {
        $this->postJson('/api/media', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.title', 'Chainsaw Man')
            ->assertJsonPath('data.total_watch_minutes', 288)
            ->assertJsonPath('message', 'Media berhasil ditambahkan.');

        $this->assertDatabaseHas('media', ['title' => 'Chainsaw Man']);
    }

    public function test_store_gagal_jika_judul_kosong(): void
    {
        $this->postJson('/api/media', $this->validPayload(['title' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title' => 'Judul media wajib diisi.']);
    }

    public function test_store_gagal_jika_judul_duplikat(): void
    {
        $this->postJson('/api/media', $this->validPayload(['title' => 'Death Note']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title' => 'Judul media sudah ada di FlipPlay.']);
    }

    public function test_store_gagal_jika_type_tidak_valid(): void
    {
        $this->postJson('/api/media', $this->validPayload(['type' => 'Manga']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_show_menampilkan_detail_beserta_pengguna_yang_mengoleksi(): void
    {
        $yourName = Media::where('title', 'Your Name.')->first();

        $this->getJson("/api/media/{$yourName->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Your Name.')
            ->assertJsonPath('data.collected_count', 2)
            ->assertJsonCount(2, 'data.collected_by')
            ->assertJsonStructure(['data' => ['collected_by' => [['id', 'name', 'collection_info' => ['is_watched', 'added_at']]]]]);
    }

    public function test_show_404_jika_media_tidak_ada(): void
    {
        $this->getJson('/api/media/999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Media dengan ID 999 tidak ditemukan.');
    }

    public function test_update_sebagian_field(): void
    {
        $media = Media::where('title', 'Death Note')->first();

        $this->patchJson("/api/media/{$media->id}", ['score' => 8.9])
            ->assertOk()
            ->assertJsonPath('data.score', 8.9)
            ->assertJsonPath('data.title', 'Death Note');
    }

    public function test_update_boleh_memakai_judulnya_sendiri_tapi_tidak_judul_lain(): void
    {
        $media = Media::where('title', 'Death Note')->first();

        $this->patchJson("/api/media/{$media->id}", ['title' => 'Death Note'])->assertOk();
        $this->patchJson("/api/media/{$media->id}", ['title' => 'Silver Spoon'])->assertStatus(422);
    }

    public function test_destroy_menghapus_media_dan_relasi_koleksinya(): void
    {
        $media = Media::where('title', 'Your Name.')->first();

        $this->deleteJson("/api/media/{$media->id}")->assertOk();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('media_user', ['media_id' => $media->id]);
        $this->getJson("/api/media/{$media->id}")->assertNotFound();
    }

    public function test_request_tanpa_header_accept_tetap_dapat_json(): void
    {
        // Simulasi Postman tanpa header Accept: application/json
        $this->post('/api/media', ['title' => ''])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['title']]);
    }
}
