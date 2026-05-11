<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id('id_chat_conversation');
            $table->string('type_conversation', 20)->default('direct');
            $table->string('titre_conversation')->nullable();
            $table->unsignedBigInteger('id_mission')->nullable();
            $table->unsignedBigInteger('created_by_utilisateur_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('id_mission', 'chat_conv_mission_fk')
                ->references('id_mission')
                ->on('missions')
                ->nullOnDelete();

            $table->foreign('created_by_utilisateur_id', 'chat_conv_creator_fk')
                ->references('id_utilisateur')
                ->on('users')
                ->nullOnDelete();

            $table->index(['type_conversation', 'last_message_at'], 'chat_conv_type_last_idx');
        });

        Schema::create('chat_conversation_participants', function (Blueprint $table) {
            $table->id('id_chat_conversation_participant');
            $table->unsignedBigInteger('id_chat_conversation');
            $table->unsignedBigInteger('id_utilisateur');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->foreign('id_chat_conversation', 'chat_part_conv_fk')
                ->references('id_chat_conversation')
                ->on('chat_conversations')
                ->cascadeOnDelete();

            $table->foreign('id_utilisateur', 'chat_part_user_fk')
                ->references('id_utilisateur')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(['id_chat_conversation', 'id_utilisateur'], 'chat_part_conv_user_uq');
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id('id_chat_message');
            $table->unsignedBigInteger('id_chat_conversation');
            $table->unsignedBigInteger('id_sender_utilisateur');
            $table->string('type_message', 20)->default('text');
            $table->text('contenu_message');
            $table->timestamps();

            $table->foreign('id_chat_conversation', 'chat_msg_conv_fk')
                ->references('id_chat_conversation')
                ->on('chat_conversations')
                ->cascadeOnDelete();

            $table->foreign('id_sender_utilisateur', 'chat_msg_sender_fk')
                ->references('id_utilisateur')
                ->on('users')
                ->cascadeOnDelete();

            $table->index(['id_chat_conversation', 'created_at'], 'chat_msg_conv_created_idx');
        });

        Schema::create('chat_message_reads', function (Blueprint $table) {
            $table->id('id_chat_message_read');
            $table->unsignedBigInteger('id_chat_message');
            $table->unsignedBigInteger('id_utilisateur');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->foreign('id_chat_message', 'chat_read_msg_fk')
                ->references('id_chat_message')
                ->on('chat_messages')
                ->cascadeOnDelete();

            $table->foreign('id_utilisateur', 'chat_read_user_fk')
                ->references('id_utilisateur')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(['id_chat_message', 'id_utilisateur'], 'chat_read_msg_user_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_reads');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversation_participants');
        Schema::dropIfExists('chat_conversations');
    }
};
