<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table untuk relasi many-to-many Media <-> User (koleksi media milik pengguna).
     * Nama mengikuti konvensi Laravel: singular kedua model, urut alfabetis -> media_user.
     */
    public function up(): void
    {
        Schema::create('media_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->boolean('is_watched')->default(false);   // data tambahan di pivot
            $table->timestamps();                             // created_at = waktu masuk koleksi

            // Lapisan pengaman terakhir: pasangan user + media tidak boleh dobel
            $table->unique(['user_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_user');
    }
};
