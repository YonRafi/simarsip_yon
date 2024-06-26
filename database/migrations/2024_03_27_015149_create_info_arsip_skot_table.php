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
        Schema::create('info_arsip_skot', function (Blueprint $table) {
            $table->string('NO_DOK_SKOT', 25)->primary();
            $table->string('NAMA', 50)->nullable();
            $table->string('NAMA_PANGGIL', 25)->nullable();
            $table->bigInteger('NIK')->nullable();
            $table->string('JENIS_KELAMIN', 15)->nullable();
            $table->string('TEMPAT_LAHIR', 25)->nullable();
            $table->date('TANGGAL_LAHIR')->nullable();
            $table->string('AGAMA', 15)->nullable();
            $table->string('STATUS_KAWIN', 15)->nullable();
            $table->string('PEKERJAAN', 25)->nullable();
            $table->string('ALAMAT_ASAL', 50)->nullable();
            $table->string('PROV_ASAL', 25)->nullable();
            $table->string('KOTA_ASAL', 25)->nullable();
            $table->string('KEC_ASAL', 25)->nullable();
            $table->string('KEL_ASAL', 25)->nullable();
            $table->string('ALAMAT', 50)->nullable();
            $table->string('PROV', 50)->nullable();
            $table->string('KOTA', 50)->nullable();
            $table->bigInteger('TAHUN_PEMBUATAN_DOK_SKOT')->nullable();
            $table->longText('FILE_LAMA')->nullable();
            $table->text('FILE_LAINNYA')->nullable();
            $table->text('FILE_SKOT')->nullable();
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
        Schema::dropIfExists('info_arsip_skot');
    }
};
