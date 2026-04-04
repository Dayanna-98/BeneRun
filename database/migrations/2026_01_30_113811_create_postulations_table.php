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
            $table->unsignedBigInteger('id_utilisateur');
            $table->enum('statut_postulation', ['en_attente', 'accepte', 'refuse', 'annule'])->default('en_attente');
            $table->text('remarque')->nullable();
            $table->timestamp('date_postulation')->useCurrent();
            $table->timestamp('date_decision')->nullable();
            $table->timestamp('date_annulation')->nullable();
            $table->foreign('id_mission')
                  ->references('id_mission')
                  ->on('missions')
                  ->cascadeOnDelete();
            $table->foreign('id_utilisateur')
                  ->references('id_utilisateur')
                  ->on('users')
                  ->cascadeOnDelete();
            $table->unique(['id_mission', 'id_utilisateur']);
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
