<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom status dari enum ke string agar support 'waiting_payment'.
     * Flow baru: pending → waiting_payment → confirmed → selesai / batal
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: reverting to enum would require raw SQL
        // This is a safe no-op since string is compatible
    }
};
