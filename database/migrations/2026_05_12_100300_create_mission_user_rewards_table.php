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
        Schema::create('mission_user_rewards', function (Blueprint $table) {
            $table->id('id_mission_user_reward');
            $table->unsignedBigInteger('id_mission');
            $table->unsignedBigInteger('id_utilisateur');
            $table->timestamp('rewarded_at');
            $table->json('details_recompense')->nullable();
            $table->timestamps();

            $table->unique(['id_mission', 'id_utilisateur']);

            $table->foreign('id_mission')
                ->references('id_mission')
                ->on('missions')
                ->cascadeOnDelete();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_user_rewards');
    }
};
