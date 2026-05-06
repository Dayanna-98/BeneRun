<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mission_emergency_message_views')) {
            Schema::drop('mission_emergency_message_views');
        }

        if (Schema::hasTable('mission_emergency_messages')) {
            Schema::drop('mission_emergency_messages');
        }

        Schema::create('mission_emergency_messages', function (Blueprint $table) {
            $table->id('id_mission_emergency_message');
            $table->unsignedBigInteger('id_mission');
            $table->unsignedBigInteger('id_evenement');
            $table->unsignedBigInteger('id_emetteur_utilisateur');
            $table->string('categorie_urgence', 50)->nullable();
            $table->text('message_urgence');
            $table->unsignedBigInteger('pris_en_charge_par_utilisateur_id')->nullable();
            $table->timestamp('pris_en_charge_le')->nullable();
            $table->timestamps();

            $table->foreign('id_mission', 'mem_msg_mission_fk')
                ->references('id_mission')
                ->on('missions')
                ->cascadeOnDelete();

            $table->foreign('id_evenement', 'mem_msg_evenement_fk')
                ->references('id_evenement')
                ->on('evenements')
                ->cascadeOnDelete();

            $table->foreign('id_emetteur_utilisateur', 'mem_msg_sender_fk')
                ->references('id_utilisateur')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('pris_en_charge_par_utilisateur_id', 'mem_msg_owner_fk')
                ->references('id_utilisateur')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('mission_emergency_message_views', function (Blueprint $table) {
            $table->id('id_mission_emergency_message_view');
            $table->unsignedBigInteger('id_mission_emergency_message');
            $table->unsignedBigInteger('id_utilisateur');
            $table->timestamp('consulte_le');
            $table->timestamps();

            $table->foreign('id_mission_emergency_message', 'mem_view_message_fk')
                ->references('id_mission_emergency_message')
                ->on('mission_emergency_messages')
                ->cascadeOnDelete();

            $table->foreign('id_utilisateur', 'mem_view_user_fk')
                ->references('id_utilisateur')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(
                ['id_mission_emergency_message', 'id_utilisateur'],
                'mission_emergency_message_views_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_emergency_message_views');
        Schema::dropIfExists('mission_emergency_messages');
    }
};
