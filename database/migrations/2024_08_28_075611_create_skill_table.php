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
        Schema::create('skill', function (Blueprint $table) {
            $table->id('id_skill');
            $table->string('content');
            $table->string('color');
            $table->unsignedBigInteger('id_master_audio');
            $table->foreign('id_master_audio')->references('id_worker')->on('master_audio')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('worker_skill', function (Blueprint $table) {
            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker')->onDelete('cascade');

            $table->unsignedBigInteger('id_skill');
            $table->foreign('id_skill')->references('id_skill')->on('skill')->onDelete('cascade');

            $table->unsignedBigInteger('id_master_audio');
            $table->foreign('id_master_audio')->references('id_worker')->on('master_audio')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill');
    }
};
//            $table->unsignedBigInteger('id_patient');
//            $table->foreign('id_patient')->references('id_user')->on('patient')->onDelete('cascade');
//
