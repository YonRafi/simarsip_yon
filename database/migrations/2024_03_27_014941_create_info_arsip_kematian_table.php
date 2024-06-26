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
        Schema::create('info_arsip_kematian', function (Blueprint $table) {
            $table->string('NO_DOK_KEMATIAN', 25)->primary();
            $table->string('NAMA', 50)->nullable();
            $table->bigInteger('NIK')->nullable();
            $table->string('TEMPAT_LAHIR', 25)->nullable();
            $table->date('TANGGAL_LAHIR')->nullable();
            $table->date('TANGGAL_MATI')->nullable();
            $table->string('TEMPAT_MATI', 25)->nullable();
            $table->string('ALAMAT', 50)->nullable();
            $table->string('JENIS_KELAMIN', 15)->nullable();
            $table->string('AGAMA', 15)->nullable();
            $table->date('TANGGAL_LAPOR')->nullable();
            $table->bigInteger('TAHUN_PEMBUATAN_DOK_KEMATIAN')->nullable();
            $table->longText('FILE_LAMA')->nullable();
            $table->longText('FILE_F201')->nullable();
            $table->longText('FILE_SK_KEMATIAN')->nullable();
            $table->longText('FILE_KK')->nullable();
            $table->longText('FILE_KTP')->nullable();
            $table->longText('FILE_KTP_SUAMI_ISTRI')->nullable();
            $table->longText('FILE_KUTIPAN_KEMATIAN')->nullable();
            $table->longText('FILE_FC_PP')->nullable();
            $table->longText('FILE_FC_DOK_PERJALANAN')->nullable();
            $table->longText('FILE_DOK_PENDUKUNG')->nullable();
            $table->longText('FILE_SPTJM')->nullable();
            $table->longText('FILE_LAINNYA')->nullable();
            $table->longText('FILE_AKTA_KEMATIAN')->nullable();
            $table->timestamps();

            $table->foreignId('ID_ARSIP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_arsip_kematian');
    }
};
