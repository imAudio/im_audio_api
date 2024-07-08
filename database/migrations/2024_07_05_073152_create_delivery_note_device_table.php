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
        Schema::create('delivery_note_device', function (Blueprint $table) {

            $table->unsignedBigInteger('id_delivery_note');
            $table->foreign('id_delivery_note')->references('id_delivery_note')->on('delivery_note');

            $table->unsignedBigInteger('id_device');
            $table->foreign('id_device')->references('id_device')->on('device');


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
        Schema::dropIfExists('delivery_nodte_device');
    }
};
