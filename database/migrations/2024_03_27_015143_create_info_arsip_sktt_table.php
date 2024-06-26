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
        Schema::create('info_arsip_sktt', function (Blueprint $table) {
            $table->string('NO_DOK_SKTT', 25)->primary();
            $table->string('NAMA', 50)->nullable();
            $table->string('JENIS_KELAMIN', 15)->nullable();
            $table->string('TEMPAT_LAHIR', 25)->nullable();
            $table->date('TANGGAL_LAHIR')->nullable();
            $table->string('AGAMA', 15)->nullable();
            $table->string('STATUS_KAWIN', 15)->nullable();
            $table->string('KEBANGSAAN', 15)->nullable();
            $table->string('NO_PASPOR', 25)->nullable();
            $table->string('HUB_KELUARGA', 25)->nullable();
            $table->string('PEKERJAAN', 25)->nullable();
            $table->string('GOLDAR', 10)->nullable();
            $table->string('ALAMAT', 50)->nullable();
            $table->string('PROV', 50)->nullable();
            $table->string('KOTA', 50)->nullable();
            $table->bigInteger('TAHUN_PEMBUATAN_DOK_SKTT')->nullable();
            $table->longText('FILE_LAMA')->nullable();
            $table->text('FILE_LAINNYA')->nullable();
            $table->text('FILE_SKTT')->nullable();
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
        Schema::dropIfExists('info_arsip_sktt');
    }
};
