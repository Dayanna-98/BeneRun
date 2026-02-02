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
            $table->unsignedBigInteger('id_course');
            $table->foreign('id_course')->references('id_course')->on('courses');
            $table->unsignedBigInteger('id_benevole');
            $table->foreign('id_benevole')->references('id_benevole')->on('benevoles');
            $table->string('titre_mission');
            $table->string('description_mission');
            $table->date('date_debut_mission');
            $table->date('date_fin_mission');
            $table->time('heure_debut_mission');
            $table->time('heure_fin_mission');
            $table->string('lieu_mission');
            $table->integer('nombre_mission');
            $table->string('statut_mission');
            $table->boolean('publie_mission');

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
