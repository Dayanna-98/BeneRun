<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatMessage $message)
    {
        $this->message->loadMissing([
            'sender:id_utilisateur,nom_utilisateur,prenom_utilisateur,email,role_utilisateur',
            'reads:id_chat_message_read,id_chat_message,id_utilisateur,read_at',
        ]);
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.conversation.'.$this->message->id_chat_conversation)];
    }

    public function broadcastAs(): string
    {
        return 'chat.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => (string) $this->message->id_chat_message,
                'conversationId' => (string) $this->message->id_chat_conversation,
                'senderId' => (string) $this->message->id_sender_utilisateur,
                'type' => $this->message->type_message,
                'text' => $this->message->contenu_message,
                'createdAt' => $this->message->created_at?->toIso8601String(),
                'sender' => [
                    'id' => (string) $this->message->sender?->id_utilisateur,
                    'firstName' => $this->message->sender?->prenom_utilisateur,
                    'lastName' => $this->message->sender?->nom_utilisateur,
                    'email' => $this->message->sender?->email,
                    'role' => $this->message->sender?->role_utilisateur,
                ],
                'readByUserIds' => $this->message->reads
                    ->pluck('id_utilisateur')
                    ->map(fn ($value) => (string) $value)
                    ->values(),
            ],
        ];
    }
}
