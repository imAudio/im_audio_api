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
        Schema::create('attribut_mcq', function (Blueprint $table) {
            $table->enum('state', ['fait', 'attente'])->default('attente');
            $table->unsignedBigInteger('id_mcq');
            $table->foreign('id_mcq')->references('id_mcq')->on('mcq');

            $table->unsignedBigInteger('id_patient');
            $table->foreign('id_patient')->references('id_user')->on('user');

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
        Schema::dropIfExists('attribut_mcq');
    }
};
