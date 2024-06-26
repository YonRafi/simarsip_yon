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
        Schema::create('info_arsip_surat_pindah', function (Blueprint $table) {
            $table->string('NO_DOK_SURAT_PINDAH', 25)->primary();
            $table->bigInteger('NO_KK')->nullable();
            $table->string('NAMA_KEPALA', 50)->nullable();
            $table->bigInteger('NIK_KEPALA')->nullable();
            $table->string('ALASAN_PINDAH', 50)->nullable();
            $table->string('ALAMAT', 50)->nullable();
            $table->string('RT')->nullable();
            $table->string('RW')->nullable();
            $table->string('PROV', 50)->nullable();
            $table->string('KOTA', 50)->nullable();
            $table->bigInteger('KODEPOS')->nullable();
            $table->string('ALAMAT_TUJUAN', 50)->nullable();
            $table->bigInteger('RT_TUJUAN')->nullable();
            $table->bigInteger('RW_TUJUAN')->nullable();
            $table->string('PROV_TUJUAN', 25)->nullable();
            $table->string('KOTA_TUJUAN', 25)->nullable();
            $table->string('KEC_TUJUAN', 25)->nullable();
            $table->string('KEL_TUJUAN', 25)->nullable();
            $table->bigInteger('KODEPOS_TUJUAN')->nullable();
            $table->bigInteger('THN_PEMBUATAN_DOK_SURAT_PINDAH')->nullable();
            $table->longText('FILE_LAMA')->nullable();
            $table->longText('FILE_SKP_WNI')->nullable();
            $table->longText('FILE_KTP_ASAL')->nullable();
            $table->longText('FILE_NIKAH_CERAI')->nullable();
            $table->longText('FILE_AKTA_KELAHIRAN')->nullable();
            $table->longText('FILE_KK')->nullable();
            $table->longText('FILE_F101')->nullable();
            $table->longText('FILE_F102')->nullable();
            $table->longText('FILE_F103')->nullable();
            $table->longText('FILE_DOK_PENDUKUNG')->nullable();
            $table->text('FILE_LAINNYA')->nullable();
            $table->text('FILE_SURAT_PINDAH')->nullable();
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
        Schema::dropIfExists('info_arsip_surat_pindah');
    }
};
