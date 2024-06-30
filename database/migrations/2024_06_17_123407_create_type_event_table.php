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
        Schema::create('type_event', function (Blueprint $table) {
            $table->id('id_type_event');
            $table->string('content');
            $table->string('default_duration');
            $table->string('background_color');
            $table->unsignedBigInteger('id_master_audio');
            $table->foreign('id_master_audio')->references('id_worker')->on('master_audio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_event');
    }
};
