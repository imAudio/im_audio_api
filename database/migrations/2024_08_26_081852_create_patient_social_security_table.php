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
        Schema::create('patient_social_security', function (Blueprint $table) {
            $table->string('social_security_number')->nullable();

            $table->unsignedBigInteger('id_patient');
            $table->foreign('id_patient')->references('id_user')->on('patient')->onDelete('cascade');

            $table->date("date_open")->nullable();
            $table->date("date_close")->nullable();
            $table->string("situation")->nullable();
            $table->string("special_situation")->nullable();
            $table->string("cash_register_code")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_social_security');
    }
};
