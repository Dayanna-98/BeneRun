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
        Schema::create('user_badges', function (Blueprint $table) {
            $table->unsignedBigInteger('id_utilisateur');  // FK vers users
            $table->unsignedBigInteger('id_badge');        // FK vers badges
            $table->timestamp('attribue_le');
            $table->timestamps();

            // Clés primaires composées
            $table->primary(['id_utilisateur', 'id_badge']);

            // Clés étrangères
            $table->foreign('id_utilisateur')
                  ->references('id_utilisateur')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('id_badge')
                  ->references('id_badge')
                  ->on('badges')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};