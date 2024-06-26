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
        Schema::create('info_arsip_pengangkatan', function (Blueprint $table) {
            $table->string('NO_DOK_PENGANGKATAN', 25)->primary();
            $table->string('NAMA_ANAK', 50)->nullable();
            $table->bigInteger('NIK')->nullable();
            $table->date('TANGGAL_LAHIR')->nullable();
            $table->string('JENIS_KELAMIN', 15)->nullable();
            $table->string('NO_PP', 25)->nullable();
            $table->date('TANGGAL_PP')->nullable();
            $table->string('NAMA_AYAH', 50)->nullable();
            $table->string('NAMA_IBU', 50)->nullable();
            $table->bigInteger('THN_PEMBUATAN_DOK_PENGANGKATAN')->nullable();
            $table->longText('FILE_LAMA')->nullable();
            $table->longText('FILE_LAINNYA')->nullable();
            $table->longText('FILE_PENGANGKATAN')->nullable();
            $table->timestamps();

            $table->foreignId('ID_ARSIP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_arsip_pengangkatan');
    }
};
