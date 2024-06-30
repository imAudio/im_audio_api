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
        Schema::create('device_modele', function (Blueprint $table) {
            $table->id('id_device_modele');
            $table->unsignedBigInteger('id_device_manufactured');
            $table->foreign('id_device_manufactured')->references('id_device_manufactured')->on('device_manufactured');

            $table->unsignedBigInteger('id_device_type');
            $table->foreign('id_device_type')->references('id_device_type')->on('device_type');

            $table->string('content')->unique();

            $table->enum('state', ['battery', 'rechargeable']);
            $table->enum('battery_type', ['10','312','13','675'])->nullable();
            $table->enum('battery_type_background_color', ['#E9EF33','#662C2C','#E57E27','#2778E5'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_modele');
    }
};
