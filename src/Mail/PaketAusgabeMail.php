<?php

namespace Hwkdo\IntranetAppPacktrack\Mail;

use App\Models\User;
use Hwkdo\IntranetAppPacktrack\Models\IntranetAppPacktrackSettings;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaketAusgabeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $empfaenger,
        public readonly User $abholer,
        public readonly int $anzahl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ihre Pakete wurden abgeholt',
        );
    }

    public function content(): Content
    {
        $settings = IntranetAppPacktrackSettings::current()?->settings;
        $oeffnungszeiten = $settings?->oeffnungszeiten ?? 'Mo–Fr 8:00–16:00 Uhr';
        $anzahlGesamt = Paket::nichtAbgeholtFuerUser($this->empfaenger->id)->count();

        return new Content(
            markdown: 'intranet-app-packtrack::emails.paket-ausgabe',
            with: [
                'oeffnungszeiten' => $oeffnungszeiten,
                'anzahlGesamt' => $anzahlGesamt,
            ],
        );
    }
}
