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
        Schema::table('delivery_note', function (Blueprint $table) {
            // Ajouter la colonne avec une valeur par défaut
            $table->unsignedBigInteger('id_device_manufactured')->default(1)->nullable();
        });

        // Mettre à jour les enregistrements existants avec une valeur valide
        DB::table('delivery_note')->update(['id_device_manufactured' => 1]);

        // Ajouter la contrainte de clé étrangère
        Schema::table('delivery_note', function (Blueprint $table) {
            $table->foreign('id_device_manufactured')
                ->references('id_device_manufactured')
                ->on('device_manufactured')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_note', function (Blueprint $table) {
            $table->dropForeign(['id_device_manufactured']);
            $table->dropColumn('id_device_manufactured');
        });
    }
};
