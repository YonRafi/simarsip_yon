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
        Schema::create('operator', function (Blueprint $table) {
            $table->id('ID_OPERATOR');
            $table->string('NAMA_OPERATOR');
            $table->string('EMAIL')->unique();
            $table->string('PASSWORD');
            $table->timestamps();

            $table->foreignId('ID_AKSES')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator');
    }
};
