<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatConversationParticipant;

Broadcast::channel('chat.user.{id}', function ($user, $id) {
    return (int) $user->id_utilisateur === (int) $id;
});

Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    return ChatConversationParticipant::query()
        ->where('id_chat_conversation', (int) $conversationId)
        ->where('id_utilisateur', (int) $user->id_utilisateur)
        ->exists();
});
