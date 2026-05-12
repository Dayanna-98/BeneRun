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
        Schema::create('badge_competence_rules', function (Blueprint $table) {
            $table->id('id_badge_competence_rule');
            $table->unsignedBigInteger('id_badge');
            $table->unsignedBigInteger('id_competence');
            $table->unsignedInteger('points_requis');
            $table->timestamps();

            $table->unique(['id_badge', 'id_competence']);

            $table->foreign('id_badge')
                ->references('id_badge')
                ->on('badges')
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
        Schema::dropIfExists('badge_competence_rules');
    }
};
