<?php

namespace Hwkdo\IntranetAppPacktrack\Mail;

use App\Models\User;
use Hwkdo\IntranetAppPacktrack\Models\IntranetAppPacktrackSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaketReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $empfaenger,
        public readonly int $anzahlOffen,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Erinnerung: Pakete an der Poststelle',
        );
    }

    public function content(): Content
    {
        $settings = IntranetAppPacktrackSettings::current()?->settings;
        $oeffnungszeiten = $settings?->oeffnungszeiten ?? 'Mo–Fr 8:00–16:00 Uhr';

        return new Content(
            markdown: 'intranet-app-packtrack::emails.paket-reminder',
            with: [
                'oeffnungszeiten' => $oeffnungszeiten,
            ],
        );
    }
}
