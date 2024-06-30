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
        Schema::create('audio_center', function (Blueprint $table) {
            $table->id('id_audio_center');
            $table->string('name');
            $table->string('city');
            $table->string('address');
            $table->string('postal_code');
            $table->unsignedBigInteger('id_master_audio');
            $table->foreign('id_master_audio')->references('id_worker')->on('master_audio');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_center');
    }
};
