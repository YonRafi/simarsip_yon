<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('history_pelayanan', function (Blueprint $table) {
            $table->bigInteger('ID_HISTORY')->primary();
            $table->string('KEGIATAN', 50);
            $table->date('TANGGAL_PELAYAN');
            $table->timestamps();

            $table->foreignId('ID_ARSIP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_pelayanan');
    }
};
