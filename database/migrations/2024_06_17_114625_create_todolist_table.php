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
        Schema::create('to_do_list', function (Blueprint $table) {
            $table->id('id_to_do_list');
            $table->string('content');
            $table->string('category');
            $table->date('date')->nullable();
            $table->boolean('is_deleted')->default(0);

            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker');

            $table->unsignedBigInteger('id_audio_center');
            $table->foreign('id_audio_center')->references('id_audio_center')->on('audio_center');

            $table->unsignedBigInteger('id_user')->nullable();
            $table->foreign('id_user')->references('id_user')->on('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todolist');
    }
};
