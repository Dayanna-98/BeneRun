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
            $table->id('id_affectation');
            $table->unsignedBigInteger('id_benevole');
            $table->foreign('id_benevole')->references('id_benevole')->on('benevoles');
            $table->unsignedBigInteger('id_mission');
            $table->foreign('id_mission')->references('id_mission')->on('missions');
            $table->string('statut_affectation');
            $table->string('remarque_affectation');
            $table->boolean('estResponsable_affectation');
            
            $table->timestamps();
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
