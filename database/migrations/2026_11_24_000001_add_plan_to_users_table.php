<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel users sudah dibuat oleh migration bawaan Laravel,
     * jadi kolom baru ditambahkan dengan Schema::table() (alter table).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Paket langganan: Free (maks 5 media di koleksi) atau Premium (maks 100)
            $table->string('plan', 10)->default('Free')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }
};
