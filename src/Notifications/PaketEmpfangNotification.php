<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppPacktrack\Notifications;

use App\Models\User;
use Hwkdo\IntranetAppBase\Notifications\IntranetNotification;
use Hwkdo\IntranetAppPacktrack\IntranetAppPacktrack;
use Hwkdo\IntranetAppPacktrack\Models\IntranetAppPacktrackSettings;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Illuminate\Notifications\Messages\MailMessage;

class PaketEmpfangNotification extends IntranetNotification
{
    public function __construct(
        public readonly User $empfaenger,
        public readonly string $packetdienst,
        public readonly int $anzahl,
        public readonly ?string $lieferant,
        public readonly ?string $bemerkung,
    ) {
        parent::__construct();
    }

    public function typeKey(): string
    {
        return 'packtrack.paket_empfang';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $anzahlGesamt = Paket::nichtAbgeholtFuerUser($this->empfaenger->id)->count();
        $settings = IntranetAppPacktrackSettings::current()?->settings;
        $oeffnungszeiten = $settings?->oeffnungszeiten ?? 'Mo–Fr 8:00–16:00 Uhr';

        return (new MailMessage)
            ->subject('Pakete an der Poststelle')
            ->line("Es {$this->anzahl} Paket(e) für Sie eingegangen ({$this->packetdienst}).")
            ->line("Abholung insgesamt offen: {$anzahlGesamt}")
            ->line("Öffnungszeiten: {$oeffnungszeiten}")
            ->action('Packtrack öffnen', route('apps.packtrack.index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->inboxPayload(
            title: 'Pakete an der Poststelle',
            body: "{$this->anzahl} Paket(e) eingegangen ({$this->packetdienst}).",
            url: route('apps.packtrack.index'),
            appIdentifier: IntranetAppPacktrack::identifier(),
        );
    }
}
