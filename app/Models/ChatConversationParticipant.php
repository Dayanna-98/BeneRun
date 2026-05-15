<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversationParticipant extends Model
{
    protected $table = 'chat_conversation_participants';

    protected $primaryKey = 'id_chat_conversation_participant';

    protected $fillable = [
        'id_chat_conversation',
        'id_utilisateur',
        'joined_at',
        'last_read_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_read_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(
            ChatConversation::class,
            'id_chat_conversation',
            'id_chat_conversation'
        );
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
