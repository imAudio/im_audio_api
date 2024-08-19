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
        Schema::table('device_manufactured', function (Blueprint $table) {

            $table->unsignedBigInteger('id_master_audio')->nullable();;
            $table->foreign('id_master_audio')->references('id_worker')->on('master_audio');
        });


    }

    public function down(): void
    {

    }
};
