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
        Schema::create('info_arsip_perceraian', function (Blueprint $table) {
            $table->string('NO_DOK_PERCERAIAN', 25)->primary();
            $table->string('NAMA_PRIA', 50)->nullable();
            $table->string('NAMA_WANITA', 50)->nullable();
            $table->string('ALAMAT_PRIA', 50)->nullable();
            $table->string('ALAMAT_WANITA', 50)->nullable();
            $table->string('NO_PP', 25)->nullable();
            $table->date('TANGGAL_PP')->nullable();
            $table->string('DOMISILI_CERAI', 25)->nullable();
            $table->string('NO_PERKAWINAN', 25)->nullable();
            $table->date('TANGGAL_DOK_PERKAWINAN')->nullable();
            $table->bigInteger('TAHUN_PEMBUATAN_DOK_PERCERAIAN')->nullable();
            $table->longText('FILE_LAMA')->nullable();
            $table->longText('FILE_F201')->nullable();
            $table->longText('FILE_FC_PP')->nullable();
            $table->longText('FILE_KUTIPAN_PERKAWINAN')->nullable();
            $table->longText('FILE_KTP')->nullable();
            $table->longText('FILE_KK')->nullable();
            $table->longText('FILE_SPTJM')->nullable();
            $table->longText('FILE_LAINNYA')->nullable();
            $table->longText('FILE_AKTA_PERCERAIAN')->nullable();
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
        Schema::dropIfExists('info_arsip_perceraian');
    }
};
