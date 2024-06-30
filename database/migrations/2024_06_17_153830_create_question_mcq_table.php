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
        Schema::create('question', function (Blueprint $table) {
            $table->id('id_question');
            $table->string('content');
            $table->enum('type', ['choice', 'note']);

            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker');

            $table->timestamps();
        });
        Schema::create('mcq', function (Blueprint $table) {
            $table->id('id_mcq');
            $table->string('content');
            $table->enum('type', ['type1', 'type2']);
            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker');
            $table->timestamps();
        });

        Schema::create('relate', function (Blueprint $table) {

            $table->unsignedBigInteger('id_mcq');
            $table->foreign('id_mcq')->references('id_mcq')->on('mcq');

            $table->unsignedBigInteger('id_question');
            $table->foreign('id_question')->references('id_question')->on('question');

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
        Schema::dropIfExists('question_mcq');
    }
};
