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
        Schema::create('device_transfer', function (Blueprint $table) {
            $table->unsignedBigInteger('id_device')->references('id_device')->on('device')->onDelete('cascade');
            $table->foreign('id_device')->references('id_device')->on('device');

            $table->unsignedBigInteger('id_audio_center');
            $table->foreign('id_audio_center')->references('id_audio_center')->on('audio_center');

            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_transfer');
    }
};
