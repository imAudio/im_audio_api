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
        Schema::create('patient_phone', function (Blueprint $table) {
            $table->id('id_patient_phone');
            $table->string('phone')->nullable();
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
        Schema::dropIfExists('patient_phone_table_update_patient');
    }
};
