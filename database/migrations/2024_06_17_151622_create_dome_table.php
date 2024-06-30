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
        Schema::create('device_dome', function (Blueprint $table) {
            $table->id('id_device_dome');
            $table->string('size');
            $table->enum('state', ['open', 'close']);


            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('worker');

            $table->unsignedBigInteger('id_device_manufactured');
            $table->foreign('id_device_manufactured')->references('id_device_manufactured')->on('device_manufactured');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dom');
    }
};
