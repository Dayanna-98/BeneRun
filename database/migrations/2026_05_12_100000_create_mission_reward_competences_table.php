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
        Schema::create('mission_reward_competences', function (Blueprint $table) {
            $table->unsignedBigInteger('id_mission');
            $table->unsignedBigInteger('id_competence');
            $table->unsignedInteger('points_gagnes');
            $table->timestamps();

            $table->primary(['id_mission', 'id_competence']);

            $table->foreign('id_mission')
                ->references('id_mission')
                ->on('missions')
                ->cascadeOnDelete();

            $table->foreign('id_competence')
                ->references('id_competence')
                ->on('competences')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_reward_competences');
    }
};
