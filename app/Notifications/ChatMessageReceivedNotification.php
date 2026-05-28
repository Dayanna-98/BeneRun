<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ChatMessageReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $senderName,
        private readonly string $messagePreview,
        private readonly string $actionUrl,
        private readonly ?string $conversationName = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->from(
                (string) config('mail.from.address', 'noreply.benerun@gmail.com'),
                (string) config('mail.from.name', config('app.name', 'Béné\'Run')),
            )
            ->subject('Nouveau message de '.$this->senderName.' - Béné\'Run')
            ->view('emails.chat-message-received', [
                'recipient' => $notifiable,
                'senderName' => $this->senderName,
                'messagePreview' => Str::limit($this->messagePreview, 220),
                'actionUrl' => $this->actionUrl,
                'conversationName' => $this->conversationName,
            ]);
    }
}
