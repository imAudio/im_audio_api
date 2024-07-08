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
        Schema::create("delivery_note", function (Blueprint $table) {
            $table->id("id_delivery_note");
            $table->string("name");
            $table->integer("number_device");
            $table->unsignedBigInteger('id_audio_center');
            $table->foreign('id_audio_center')->references('id_audio_center')->on('audio_center');

            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("delivery_note");
    }
};
