<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MissionEmergencyReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $senderName,
        private readonly string $missionName,
        private readonly string $eventName,
        private readonly string $category,
        private readonly string $messagePreview,
        private readonly string $actionUrl,
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
            ->subject('Urgence mission - '.$this->missionName.' - Béné\'Run')
            ->view('emails.mission-emergency-received', [
                'recipient' => $notifiable,
                'senderName' => $this->senderName,
                'missionName' => $this->missionName,
                'eventName' => $this->eventName,
                'category' => $this->category,
                'messagePreview' => Str::limit($this->messagePreview, 300),
                'actionUrl' => $this->actionUrl,
            ]);
    }
}
