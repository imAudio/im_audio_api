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
        Schema::create('device_manufactured', function (Blueprint $table) {
            $table->id('id_device_manufactured');
            $table->string('content');
            $table->timestamps();
        });

        Schema::create('device_type', function (Blueprint $table) {
            $table->id('id_device_type');
            $table->string('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_manufactured_y_type');
    }
};
