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
        Schema::create('device_model_characteristic', function (Blueprint $table) {

            $table->string('device_content');
            $table->foreign('device_content')->references('content')->on('device_model');

            $table->unsignedBigInteger('id_device_characteristic');
            $table->foreign('id_device_characteristic')->references('id_device_characteristic')->on('device_characteristic');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_model_characteristic');
    }
};
