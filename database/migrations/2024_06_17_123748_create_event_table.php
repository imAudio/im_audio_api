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
        Schema::create('event', function (Blueprint $table) {
            $table->id('id_event');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->text('description');
            $table->string('state');
            $table->string('days')->nullable();
            $table->time('weekly_start')->nullable();
            $table->time('weekly_end')->nullable();

            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker');

            $table->unsignedBigInteger('id_type_event');
            $table->foreign('id_type_event')->references('id_type_event')->on('type_event');

            $table->unsignedBigInteger('id_user')->nullable();
            $table->foreign('id_user')->references('id_user')->on('user');

            $table->unsignedBigInteger('id_audio_center');
            $table->foreign('id_audio_center')->references('id_audio_center')->on('audio_center');

            $table->timestamp('created')->useCurrent();
            $table->timestamp('updated')->useCurrent()->useCurrentOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};
