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
        Schema::create('certificats', function (Blueprint $table) {
            $table->id('id_certificat');
            $table->unsignedBigInteger('id_utilisateur');  // FK vers users
            $table->string('titre_certificat', 150);
            $table->string('emetteur_certificat', 150)->nullable();
            $table->date('date_emission_certificat')->nullable();
            $table->date('date_expiration_certificat')->nullable();
            $table->enum('type_certificat', ['platform', 'external'])->default('platform');
            $table->enum('statut_certificat', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('chemin_fichier_certificat', 500)->nullable();
            $table->timestamps();

            // Clé étrangère
            $table->foreign('id_utilisateur')
                  ->references('id_utilisateur')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificats');
    }
};