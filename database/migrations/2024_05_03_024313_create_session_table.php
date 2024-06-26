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
        Schema::create('session', function (Blueprint $table) {
            $table->id('ID_SESSION');
            $table->string('JWT_TOKEN');
            $table->string('STATUS');
            $table->dateTime('EXPIRED_AT');
            $table->timestamps();

            $table->foreignId('ID_OPERATOR')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session');
    }
};
