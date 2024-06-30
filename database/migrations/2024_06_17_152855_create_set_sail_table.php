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
        Schema::create('set_sail', function (Blueprint $table) {

            $table->string('size_earpiece');
            $table->timestamps();

            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker');

            $table->unsignedBigInteger('id_device');
            $table->foreign('id_device')->references('id_device')->on('device');

            $table->unsignedBigInteger('id_device_dome');
            $table->foreign('id_device_dome')->references('id_device_dome')->on('device_dome');

            $table->unsignedBigInteger('id_patient');
            $table->foreign('id_patient')->references('id_user')->on('patient');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('set_sail');
    }
};
