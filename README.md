# FlipPlay API (Modul 4 – Laravel 12 REST API)

Lanjutan dari FlipPlay Engine (Modul 3). Class `Media` dan `User` beserta koleksi media
sekarang menjadi Model Eloquent yang tersimpan di MySQL, lalu diekspos sebagai REST API.

## Isi folder ini

Folder ini berisi file yang **ditimpakan** ke project Laravel 12 baru. Struktur foldernya sama
dengan project Laravel, jadi tinggal di-copy ke root project dan pilih *Replace*.

```
app/Http/Controllers/  MediaController (CRUD), UserController, CollectionController (custom action)
app/Http/Requests/     StoreMediaRequest, UpdateMediaRequest, AddToCollectionRequest, UpdateCollectionItemRequest
app/Http/Resources/    MediaResource, UserResource (pakai whenLoaded / whenCounted / whenPivotLoaded)
app/Models/            Media, User (relasi belongsToMany)
bootstrap/app.php      routing api + error JSON (404 & 422)
database/migrations/   add_plan_to_users, create_media, create_media_user (pivot)
database/factories/    MediaFactory
database/seeders/      MediaSeeder (data Modul 3), UserSeeder, DatabaseSeeder
routes/api.php         semua endpoint
tests/Feature/         MediaApiTest, CollectionApiTest (22 test)
postman/               FlipPlay-API.postman_collection.json
```

## Langkah pemasangan (Windows + Laragon/XAMPP)

1. Buat project Laravel 12 dan aktifkan routing API:
   ```powershell
   composer create-project laravel/laravel:^12.0 flipplay-api
   cd flipplay-api
   php artisan install:api
   ```
   Saat `install:api` bertanya soal migration, jawab `no` dulu (database belum diatur).

2. Copy semua folder dari zip ini (`app`, `bootstrap`, `database`, `routes`, `tests`, `postman`,
   `README.md`) ke dalam folder `flipplay-api`, lalu pilih **Replace** saat ditanya.
   Atau lewat PowerShell (sesuaikan path folder hasil ekstrak):
   ```powershell
   Copy-Item -Path "..\flipplay-api-modul4\*" -Destination . -Recurse -Force
   ```

3. Buat database `flipplay_api` di phpMyAdmin/HeidiSQL, lalu ubah `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=flipplay_api
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Buat tabel dan isi data awal:
   ```powershell
   php artisan config:clear
   php artisan migrate:fresh --seed
   ```

5. Cek route dan jalankan server:
   ```powershell
   php artisan route:list --path=api
   php artisan serve
   ```

6. Jalankan test otomatis (memakai SQLite in-memory, tidak mengganggu database MySQL):
   ```powershell
   php artisan test
   ```

## Endpoint

Base URL: `http://127.0.0.1:8000/api`

| Method | Endpoint | Fungsi | Status sukses | Status gagal |
|---|---|---|---|---|
| GET | `/media` | Daftar media + pagination. Query: `search`, `type`, `genre`, `per_page` | 200 | – |
| POST | `/media` | Tambah media | 201 | 422 (judul kosong/duplikat, type salah) |
| GET | `/media/{media}` | Detail media + pengguna yang mengoleksi | 200 | 404 |
| PATCH | `/media/{media}` | Ubah sebagian data media | 200 | 404, 422 |
| DELETE | `/media/{media}` | Hapus media (relasi koleksi ikut terhapus) | 200 | 404 |
| GET | `/users` | Daftar pengguna + jumlah koleksi | 200 | – |
| GET | `/users/{user}` | Detail pengguna + koleksi | 200 | 404 |
| GET | `/users/{user}/collection` | Koleksi media milik pengguna | 200 | 404 |
| POST | `/users/{user}/collection` | Tambah media ke koleksi. Body `{"media_id": 1}` | 201 | 409 (sudah ada / koleksi penuh), 422 |
| PATCH | `/users/{user}/collection/{media}` | Tandai sudah ditonton. Body `{"is_watched": true}` | 200 | 404, 422 |
| DELETE | `/users/{user}/collection/{media}` | Hapus media dari koleksi | 200 | 404 |

Contoh body tambah media:

```json
{
  "title": "Chainsaw Man",
  "type": "TV",
  "genres": ["Action", "Fantasy"],
  "year": 2022,
  "score": 8.5,
  "episodes": 12,
  "duration": 24,
  "synopsis": "Denji menjadi pemburu iblis."
}
```

`type` hanya `TV` atau `Movie`. `duration` = menit per episode (TV) atau durasi film (Movie).
`episodes` boleh tidak dikirim untuk Movie (otomatis 1).

## Data awal (seeder)

- 12 media dari simulasi Modul 3 (ID 1–12, ID 6 = *Your Name.*).
- Pengguna ID 1 **Ega** (paket Free, maks 5 media) dengan koleksi Frieren, Your Name., Bocchi the Rock!.
- Pengguna ID 2 **Dina** (paket Premium, maks 100 media) dengan koleksi Your Name., Death Note.

## Pengujian dengan Postman

1. Jalankan `php artisan migrate:fresh --seed` supaya datanya bersih.
2. Import `postman/FlipPlay-API.postman_collection.json` (File → Import).
3. Jalankan request satu per satu dari atas, atau klik kanan collection → **Run collection**.
   Setiap request sudah berisi test status code. Request berawalan **[GAGAL]** adalah skenario gagal
   yang memang diharapkan (422, 404, 409).
4. Request "Tambah media baru" otomatis menyimpan ID media baru ke variabel `media_id`.

## Pemetaan ke ketentuan demo

| Ketentuan | Penerapan |
|---|---|
| Repositori GitHub + project Laravel | Lihat bagian "Upload ke GitHub" |
| Minimal dua entitas dengan relasi Eloquent | `Media` ↔ `User` dengan `belongsToMany` lewat pivot `media_user` |
| Migration lengkap (foreign key / pivot) | `create_media_table`, `create_media_user_table` (2 foreign key + unique), `add_plan_to_users_table` |
| Controller CRUD Media | `MediaController`: index, store, show, update, destroy |
| Form Request Validation | `StoreMediaRequest` (judul wajib & unik), `UpdateMediaRequest` (`sometimes` + `unique()->ignore()`), `AddToCollectionRequest`, `UpdateCollectionItemRequest` |
| API Resource + `whenLoaded` | `MediaResource` (`collected_by`), `UserResource` (`collection`) |
| Semua endpoint di `routes/api.php` | `apiResource` + group custom action; cek dengan `php artisan route:list --path=api` |
| Custom action yang mengubah relasi | `CollectionController`: `attach()`, `updateExistingPivot()`, `detach()`, dengan `DB::transaction()` + `lockForUpdate()` dan respons 409 |

Fitur tambahan dari materi modul: accessor (`total_watch_minutes`), mutator (merapikan judul),
query scope (`search`, `ofType`, `hasGenre`), casting JSON → array (`genres`), seeder & factory,
pagination, dan pesan error 404 yang jelas.

## Upload ke GitHub

1. Buat repository baru di github.com (misalnya `flipplay-api`), tanpa README.
2. Di folder project:
   ```powershell
   git init
   git add .
   git commit -m "FlipPlay REST API Modul 4"
   git branch -M main
   git remote add origin https://github.com/<username>/flipplay-api.git
   git push -u origin main
   ```
   File `.env` dan folder `vendor` otomatis tidak ikut ter-upload karena sudah ada di `.gitignore` bawaan Laravel.
