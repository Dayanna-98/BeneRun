<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatConversationChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatConversation $conversation)
    {
        $this->conversation->loadMissing([
            'participants.utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email,role_utilisateur',
            'lastMessage:id_chat_message,id_chat_conversation,id_sender_utilisateur,contenu_message,created_at',
        ]);
    }

    public function broadcastOn(): array
    {
        return $this->conversation->participants
            ->map(fn ($participant) => new PrivateChannel('chat.user.'.$participant->id_utilisateur))
            ->values()
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'chat.conversation.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'conversationId' => (string) $this->conversation->id_chat_conversation,
        ];
    }
}
