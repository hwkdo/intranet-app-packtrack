<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppPacktrack\Notifications;

use App\Models\User;
use Hwkdo\IntranetAppBase\Notifications\IntranetNotification;
use Hwkdo\IntranetAppPacktrack\IntranetAppPacktrack;
use Illuminate\Notifications\Messages\MailMessage;

class PaketAusgabeNotification extends IntranetNotification
{
    public function __construct(
        public readonly User $empfaenger,
        public readonly int $anzahl,
    ) {
        parent::__construct();
    }

    public function typeKey(): string
    {
        return 'packtrack.paket_ausgabe';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pakete ausgegeben')
            ->line("{$this->anzahl} Paket(e) wurden an Sie ausgegeben.")
            ->action('Packtrack öffnen', route('apps.packtrack.index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->inboxPayload(
            title: 'Pakete ausgegeben',
            body: "{$this->anzahl} Paket(e) wurden ausgegeben.",
            url: route('apps.packtrack.index'),
            appIdentifier: IntranetAppPacktrack::identifier(),
        );
    }
}
