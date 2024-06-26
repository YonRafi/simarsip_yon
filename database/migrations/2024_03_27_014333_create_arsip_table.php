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
        Schema::create('arsip', function (Blueprint $table) {
            $table->id('ID_ARSIP');
            $table->bigInteger('JUMLAH_BERKAS')->nullable();
            $table->bigInteger('NO_BUKU')->nullable();
            $table->bigInteger('NO_RAK')->nullable();
            $table->bigInteger('NO_BARIS')->nullable();
            $table->bigInteger('NO_BOKS')->nullable();
            $table->string('LOK_SIMPAN', 25)->nullable();
            $table->date('TANGGAL_PINDAI')->nullable();
            $table->text('KETERANGAN')->nullable();
            $table->timestamps();

            // Add foreign key constraints if necessary
            $table->foreignId('ID_AKSES')->nullable();
            $table->foreignId('ID_HISTORY')->nullable();
	        $table->foreignId('ID_DOKUMEN')->nullable();
	        $table->foreignId('NO_DOK_PENGANGKATAN')->nullable();
            $table->foreignId('NO_DOK_SURAT_PINDAH')->nullable();
            $table->foreignId('NO_DOK_PERCERAIAN')->nullable();
            $table->foreignId('NO_DOK_PENGESAHAN')->nullable();
            $table->foreignId('NO_DOK_KEMATIAN')->nullable();
            $table->foreignId('NO_DOK_KELAHIRAN')->nullable();
            $table->foreignId('NO_DOK_PENGAKUAN')->nullable();
            $table->foreignId('NO_DOK_PERKAWINAN')->nullable();
            $table->foreignId('NO_DOK_KK')->nullable();
            $table->foreignId('NO_DOK_SKOT')->nullable();
            $table->foreignId('NO_DOK_SKTT')->nullable();
            $table->foreignId('NO_DOK_KTP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip');
    }
};
