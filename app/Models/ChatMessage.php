<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';
    protected $primaryKey = 'id_chat_message';

    protected $fillable = [
        'id_chat_conversation',
        'id_sender_utilisateur',
        'type_message',
        'contenu_message',
    ];

    public function conversation()
    {
        return $this->belongsTo(
            ChatConversation::class,
            'id_chat_conversation',
            'id_chat_conversation'
        );
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'id_sender_utilisateur', 'id_utilisateur');
    }

    public function reads()
    {
        return $this->hasMany(ChatMessageRead::class, 'id_chat_message', 'id_chat_message');
    }
}
