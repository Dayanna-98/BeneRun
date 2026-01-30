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
        Schema::create('postulations', function (Blueprint $table) {
            $table->id('id_postulation');
            $table->unsignedBigInteger('id_mission');
            $table->foreign('id_mission')->references('id_mission')->on('missions');
            $table->unsignedBigInteger('id_benevole');
            $table->foreign('id_benevole')->references('id_benevole')->on('benevoles');
            $table->dateTime('date_postulation');
            $table->dateTime('date_acceptation_refus_postulation');
            $table->dateTime('date_annulation_postulation');
            $table->string('remarque_postulation');
            $table->boolean('presence_postulation');
            $table->string('commentaire_postulation');
            $table->enum('statut_postulation', ['en_attente', 'accepte', 'refuse'])->default('en_attente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulations');
    }
};
