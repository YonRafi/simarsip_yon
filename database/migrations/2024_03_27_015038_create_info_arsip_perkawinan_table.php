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
        Schema::create('info_arsip_perkawinan', function (Blueprint $table) {
            $table->string('NO_DOK_PERKAWINAN', 25)->primary();
            $table->string('NAMA_PRIA', 50)->nullable();
            $table->string('NAMA_WANITA', 50)->nullable();
            $table->date('TANGGAL_DOK_PERKAWINAN')->nullable();
            $table->string('TEMPAT_KAWIN', 25)->nullable();
            $table->string('AGAMA_KAWIN', 15)->nullable();
            $table->string('AYAH_PRIA', 50)->nullable();
            $table->string('IBU_PRIA', 50)->nullable();
            $table->string('AYAH_WANITA', 50)->nullable();
            $table->string('IBU_WANITA', 50)->nullable();
            $table->bigInteger('TAHUN_PEMBUATAN_DOK_PERKAWINAN')->nullable();
            $table->longText('FILE_LAMA')->nullable();
            $table->longText('FILE_F201')->nullable();
            $table->longText('FILE_FC_SK_KAWIN')->nullable();
            $table->longText('FILE_FC_PASFOTO')->nullable();
            $table->longText('FILE_KTP')->nullable();
            $table->longText('FILE_KK')->nullable();
            $table->longText('FILE_AKTA_KEMATIAN')->nullable();
            $table->longText('FILE_AKTA_PERCERAIAN')->nullable();
            $table->longText('FILE_SPTJM')->nullable();
            $table->longText('FILE_LAINNYA')->nullable();
            $table->longText('FILE_AKTA_PERKAWINAN')->nullable();
            $table->timestamps();

            $table->foreignId('ID_ARSIP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_arsip_perkawinan');
    }
};
