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
        Schema::create('useful_link', function (Blueprint $table) {
            $table->id('id_useful_link');
            $table->string('wording');
            $table->string('link');

            $table->unsignedBigInteger('id_worker');
            $table->foreign('id_worker')->references('id_user')->on('user')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link');
    }
};
