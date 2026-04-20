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
        Schema::create('affectations', function (Blueprint $table) {
            $table->id('id_affectation');  // PK
            $table->unsignedBigInteger('id_mission');  // FK vers missions
            $table->unsignedBigInteger('id_utilisateur');  // FK vers users
            $table->enum('statut_affectation', ['assigne', 'confirme', 'present', 'absent', 'annule'])
                  ->default('assigne');
            $table->boolean('est_responsable')->default(false);
            $table->text('remarque')->nullable();
            $table->timestamp('date_affectation')->nullable();
            $table->timestamp('date_confirmation')->nullable();
            $table->timestamp('date_presence')->nullable();
            $table->timestamps();

            // Clés étrangères
            $table->foreign('id_mission')
                  ->references('id_mission')
                  ->on('missions')
                  ->cascadeOnDelete();

            $table->foreign('id_utilisateur')
                  ->references('id_utilisateur')
                  ->on('users')
                  ->cascadeOnDelete();

            // Contrainte unique pour éviter doublons
            $table->unique(['id_mission', 'id_utilisateur']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};