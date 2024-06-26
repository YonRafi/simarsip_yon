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
        Schema::create('info_arsip_kk', function (Blueprint $table) {
            $table->string('NO_DOK_KK')->primary();
            $table->string('NAMA_KEPALA', 50)->nullable();
            $table->string('ALAMAT', 50)->nullable();
            $table->string('RT')->nullable();
            $table->string('RW')->nullable();
            $table->bigInteger('KODEPOS')->nullable();
            $table->string('PROV', 50)->nullable();
            $table->string('KOTA', 50)->nullable();
            $table->bigInteger('TAHUN_PEMBUATAN_DOK_KK')->nullable();
            $table->text('FILE_LAMA')->nullable();
            $table->text('FILE_F101')->nullable();
            $table->text('FILE_NIKAH_CERAI')->nullable();
            $table->text('FILE_SK_PINDAH')->nullable();
            $table->text('FILE_SK_PINDAH_LUAR')->nullable();
            $table->text('FILE_SK_PENGGANTI')->nullable();
            $table->text('FILE_PUTUSAN_PRESIDEN')->nullable();
            $table->text('FILE_KK_LAMA')->nullable();
            $table->text('FILE_SK_PERISTIWA')->nullable();
            $table->text('FILE_SK_HILANG')->nullable();
            $table->text('FILE_KTP')->nullable();
            $table->text('FILE_LAINNYA')->nullable();
            $table->text('FILE_KK')->nullable();
            $table->timestamps();

            $table->foreignId('ID_ARSIP')->nullable();
            $table->foreignId('ID_KELURAHAN')->nullable();
            $table->foreignId('ID_KECAMATAN')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_arsip_kk');
    }
};
