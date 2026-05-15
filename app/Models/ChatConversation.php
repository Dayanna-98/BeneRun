<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $table = 'chat_conversations';

    protected $primaryKey = 'id_chat_conversation';

    protected $fillable = [
        'type_conversation',
        'titre_conversation',
        'id_mission',
        'created_by_utilisateur_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function participants()
    {
        return $this->hasMany(
            ChatConversationParticipant::class,
            'id_chat_conversation',
            'id_chat_conversation'
        );
    }

    public function messages()
    {
        return $this->hasMany(
            ChatMessage::class,
            'id_chat_conversation',
            'id_chat_conversation'
        );
    }

    public function lastMessage()
    {
        return $this->hasOne(
            ChatMessage::class,
            'id_chat_conversation',
            'id_chat_conversation'
        )->latestOfMany('created_at');
    }
}
