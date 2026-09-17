<?php

namespace App\Notifications;

use App\Services\Minagri\MinagriEligibiliteService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouvelleEligibiliteMinagriNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $programmeSlug) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $libelle = MinagriEligibiliteService::PROGRAMMES[$this->programmeSlug] ?? $this->programmeSlug;

        return (new MailMessage)
            ->subject('TRU FARM ERP — Nouvelle éligibilité MINAGRI détectée')
            ->line("Votre exploitation est désormais éligible au programme : {$libelle}.")
            ->line('Consultez votre espace "Éligibilité MINAGRI" pour générer le formulaire de demande pré-rempli.');
    }
}
