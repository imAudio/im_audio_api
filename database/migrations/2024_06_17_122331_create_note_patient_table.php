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
        Schema::create('patient_note', function (Blueprint $table) {
            $table->id('id_patient_note');
            $table->string('content');
            $table->boolean('is_deleted')->default(0);
            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker');
            $table->unsignedBigInteger('id_patient')->nullable();
            $table->foreign('id_patient')->references('id_user')->on('patient');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_patient');
    }
};
