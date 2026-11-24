<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150)->unique();          // judul tidak boleh duplikat
            $table->string('type', 10);                       // TV atau Movie
            $table->json('genres');                           // contoh: ["Action","Drama"]
            $table->unsignedSmallInteger('year');
            $table->decimal('score', 4, 2)->nullable();       // 0.00 - 10.00
            $table->unsignedInteger('episodes')->default(1);  // Movie = 1
            $table->unsignedInteger('duration');              // menit per episode (TV) / durasi film (Movie)
            $table->text('synopsis')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
