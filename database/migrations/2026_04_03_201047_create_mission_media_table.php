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
        Schema::create('mission_medias', function (Blueprint $table) {

            $table->id('id_media_mission');
            $table->unsignedBigInteger('id_mission');
            $table->foreign('id_mission')
                ->references('id_mission')
                ->on('missions')
                ->cascadeOnDelete();
            $table->string('chemin_fichier', 500);
            $table->string('type_mime', 100);
            $table->unsignedBigInteger('taille_fichier')->nullable();
            $table->unsignedBigInteger('televerse_par_utilisateur_id')->nullable();
            $table->foreign('televerse_par_utilisateur_id')
                ->references('id_utilisateur')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_medias');
    }
};
