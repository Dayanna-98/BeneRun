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
        Schema::create('favorites', function (Blueprint $table) {
            $table->unsignedBigInteger('id_utilisateur');
            $table->unsignedBigInteger('id_mission');
            $table->primary(['id_utilisateur', 'id_mission']);
            $table->timestamps();

            // Clés étrangères
            $table->foreign('id_utilisateur')
                  ->references('id_utilisateur')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('id_mission')
                  ->references('id_mission')
                  ->on('missions')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};