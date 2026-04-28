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
        Schema::create('user_competences', function (Blueprint $table) {
        $table->unsignedBigInteger('id_utilisateur');
        $table->unsignedBigInteger('id_competence');
        $table->primary(['id_utilisateur', 'id_competence']);
        $table->foreign('id_utilisateur')
            ->references('id_utilisateur')
            ->on('users')
            ->cascadeOnDelete();
        $table->foreign('id_competence')
            ->references('id_competence')
            ->on('competences')
            ->cascadeOnDelete();
        $table->timestamps();
    });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_competences');
    }
    };
