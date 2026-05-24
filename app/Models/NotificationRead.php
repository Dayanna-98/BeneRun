<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRead extends Model
{
    protected $table = 'notification_reads';

    protected $primaryKey = 'id_notification_read';

    protected $fillable = [
        'id_utilisateur',
        'notification_key',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
