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
        Schema::create('info_arsip_kelahiran', function (Blueprint $table) {
            $table->string('NO_DOK_KELAHIRAN', 25)->primary();
            $table->string('NAMA', 50)->nullable();
            $table->string('TEMPAT_LAHIR', 25)->nullable();
            $table->date('TANGGAL_LAHIR')->nullable();
            $table->integer('ANAK_KE')->nullable();
            $table->string('NAMA_AYAH', 50)->nullable();
            $table->string('NAMA_IBU', 50)->nullable();
            $table->bigInteger('NO_KK')->nullable();
            $table->bigInteger('TAHUN_PEMBUATAN_DOK_KELAHIRAN')->nullable();
            $table->string('STATUS_KELAHIRAN', 25)->nullable();
            $table->string('STATUS_PENDUDUK', 25)->nullable();
            $table->longText('FILE_LAMA')->nullable();
            $table->longText('FILE_KK')->nullable();
            $table->longText('FILE_KTP_AYAH')->nullable();
            $table->longText('FILE_KTP_IBU')->nullable();
            $table->longText('FILE_F102')->nullable();
            $table->longText('FILE_F201')->nullable();
            $table->longText('FILE_BUKU_NIKAH')->nullable();
            $table->longText('FILE_KUTIPAN_KELAHIRAN')->nullable();
            $table->longText('FILE_SURAT_KELAHIRAN')->nullable();
            $table->longText('FILE_SPTJM_PENERBITAN')->nullable();
            $table->longText('FILE_PELAPORAN_KELAHIRAN')->nullable();
            $table->longText('FILE_LAINNYA')->nullable();
            $table->longText('FILE_AKTA_KELAHIRAN')->nullable();
            $table->timestamps();

            $table->foreignId('ID_ARSIP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_arsip_kelahiran');
    }
};
