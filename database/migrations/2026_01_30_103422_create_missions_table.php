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
        Schema::create('missions', function (Blueprint $table) {

            $table->id('id_mission');
            $table->unsignedBigInteger('id_evenement');
            $table->foreign('id_evenement')
                ->references('id_evenement')
                ->on('evenements')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('responsable_utilisateur_id');
            $table->foreign('responsable_utilisateur_id')
                ->references('id_utilisateur')
                ->on('users')
                ->cascadeOnDelete();
            $table->string('titre_mission', 150);
            $table->enum('type_mission', [
                'secours',
                'logistique',
                'accueil',
                'technique',
                'animation',
                'autre'
            ]);
            $table->text('description_mission');
            $table->date('date_mission');
            $table->time('heure_debut_mission');
            $table->time('heure_fin_mission');
            $table->string('lieu_mission', 255);
            $table->decimal('latitude_mission', 10, 7)->nullable();
            $table->decimal('longitude_mission', 10, 7)->nullable();
            $table->unsignedInteger('nombre_benevoles_max');
            $table->unsignedInteger('nombre_benevoles_backup')->default(0);
            $table->enum('statut_mission', [
                'À venir',
                'En cours',
                'Terminée',
                'Annulée'
            ])->default('À venir');
            $table->boolean('inscription_requise')->default(true);
            $table->enum('visibilite_mission', [
                'publique',
                'privée',
                'limitée'
            ])->default('publique');
            $table->text('consignes_securite')->nullable();
            $table->string('image_mission', 500)->nullable();
            $table->timestamp('publie_le_mission')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};