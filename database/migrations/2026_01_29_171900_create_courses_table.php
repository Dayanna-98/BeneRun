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
        Schema::create('courses', function (Blueprint $table) {
            $table->id('id_course');
            $table->string('nom_course');
            $table->string('lieu_course');
            $table->string('informations_course');
            $table->date('date_debut_course');
            $table->date('date_fin_course');
            $table->time('heure_debut_course');
            $table->time('heure_fin_course');
            $table->boolean('annule_course')->default(false);
            $table->date('date_annulation_course');
            $table->string('raison_annulation_course');
            $table->boolean('publie_course')->default(false);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
