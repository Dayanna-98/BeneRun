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
        Schema::create('evenements', function (Blueprint $table) {
            $table->id('id_evenement');
            $table->string('nom_evenement');
            $table->text('description_evenement');
            $table->date('date_debut_evenement');
            $table->date('date_fin_evenement');
            $table->time('heure_debut_evenement')->nullable();
            $table->time('heure_fin_evenement')->nullable();
            $table->string('lieu_evenement');
            $table->decimal('latitude_evenement', 10, 7)->nullable();
            $table->decimal('longitude_evenement', 10, 7)->nullable();
            $table->string('organisateur_evenement');
            $table->string('image_evenement')->nullable();
            $table->unsignedInteger('nombre_benevoles_requis')->default(0);
            $table->boolean('est_annule_evenement')->default(false);
            $table->date('date_annulation_evenement')->nullable();
            $table->string('raison_annulation_evenement')->nullable();
            $table->boolean('est_publie_evenement')->default(false);
            $table->unsignedBigInteger('cree_par_utilisateur_id');
            $table->foreign('cree_par_utilisateur_id')
              ->references('id_utilisateur')
              ->on('users')
              ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
