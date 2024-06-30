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
        Schema::create('device', function (Blueprint $table) {
            $table->id('id_device');
            $table->string('serial_number');
            $table->enum('state', ['ass', 'stock','trying','to_invoice']);

            $table->unsignedBigInteger('id_device_color');
            $table->foreign('id_device_color')->references('id_device_color')->on('device_color');

            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker');

            $table->string('device_content');
            $table->foreign('device_content')->references('content')->on('device_modele');

            $table->unsignedBigInteger('id_audio_center');
            $table->foreign('id_audio_center')->references('id_audio_center')->on('audio_center');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device');
    }
};
