<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessageRead extends Model
{
    protected $table = 'chat_message_reads';
    protected $primaryKey = 'id_chat_message_read';

    protected $fillable = [
        'id_chat_message',
        'id_utilisateur',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'id_chat_message', 'id_chat_message');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
