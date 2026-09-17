<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Alerte solidarité coopérative (T084) : un incident critique signalé chez
 * un membre est notifié au(x) gérant(s) de sa coopérative, pour permettre
 * une réaction rapide (épizootie, mortalité massive) auprès des membres
 * voisins.
 */
class AlerteSolidariteNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Incident $incident, private readonly string $nomMembre) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('TRU FARM ERP — Alerte solidarité coopérative')
            ->line("Un incident {$this->incident->gravite} ({$this->incident->type}) a été signalé chez le membre '{$this->nomMembre}'.")
            ->line($this->incident->description ?? 'Aucune description fournie.')
            ->line('Contactez le membre concerné et informez les membres voisins si nécessaire (risque épizootique).');
    }
}
