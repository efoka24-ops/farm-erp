<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CodeOtpNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('TRU FARM ERP — Code de connexion')
            ->line("Votre code de connexion est : {$this->code}")
            ->line('Ce code expire dans 10 minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.");
    }
}
