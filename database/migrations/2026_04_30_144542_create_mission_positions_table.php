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
        Schema::create('mission_positions', function (Blueprint $table) {
            $table->id('id_position');
            $table->unsignedBigInteger('id_mission');
            $table->unsignedBigInteger('id_utilisateur');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();

            $table->foreign('id_mission')->references('id_mission')->on('missions')->cascadeOnDelete();
            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('users')->cascadeOnDelete();
            $table->unique(['id_mission', 'id_utilisateur']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_positions');
    }
};
