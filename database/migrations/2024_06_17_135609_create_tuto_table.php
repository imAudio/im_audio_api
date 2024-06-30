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
        Schema::create('tuto', function (Blueprint $table) {
            $table->id('id_tuto');
            $table->string('link');
            $table->unsignedBigInteger('id_master_audio');
            $table->foreign('id_master_audio')->references('id_worker')->on('master_audio');

            $table->string('device_content');
            $table->foreign('device_content')->references('content')->on('device_modele');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuto');
    }
};
