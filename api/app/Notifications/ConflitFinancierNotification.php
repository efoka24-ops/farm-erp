<?php

namespace App\Notifications;

use App\Models\SyncConflict;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConflitFinancierNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly SyncConflict $conflit) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('TRU FARM ERP — Conflit de synchronisation financier')
            ->line("Un conflit a été détecté sur {$this->conflit->entity_type} #{$this->conflit->entity_id}.")
            ->line('Ce type de donnée requiert un arbitrage manuel de votre part.')
            ->action('Arbitrer le conflit', url("/conflits/{$this->conflit->id}"));
    }
}
