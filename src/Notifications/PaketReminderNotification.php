<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppPacktrack\Notifications;

use App\Models\User;
use Hwkdo\IntranetAppBase\Notifications\IntranetNotification;
use Hwkdo\IntranetAppPacktrack\IntranetAppPacktrack;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Illuminate\Notifications\Messages\MailMessage;

class PaketReminderNotification extends IntranetNotification
{
    public function __construct(
        public readonly User $empfaenger,
        public readonly int $anzahlOffen,
    ) {
        parent::__construct();
    }

    public function typeKey(): string
    {
        return 'packtrack.paket_reminder';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Erinnerung: Pakete abholen')
            ->line("Sie haben {$this->anzahlOffen} offene Paket(e) an der Poststelle.")
            ->action('Packtrack öffnen', route('apps.packtrack.index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->inboxPayload(
            title: 'Pakete warten auf Abholung',
            body: "{$this->anzahlOffen} offene Paket(e).",
            url: route('apps.packtrack.index'),
            appIdentifier: IntranetAppPacktrack::identifier(),
        );
    }
}
