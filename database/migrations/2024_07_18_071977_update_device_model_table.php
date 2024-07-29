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

        Schema::table('device_model', function (Blueprint $table) {
            $table->string('all_taxes_combined')->nullable();
            $table->string('duty_free')->nullable();
            $table->string('lpp_left')->nullable();
            $table->string('lpp_right')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
