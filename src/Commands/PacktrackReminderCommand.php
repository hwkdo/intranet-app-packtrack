<?php

namespace Hwkdo\IntranetAppPacktrack\Commands;

use Carbon\Carbon;
use Hwkdo\IntranetAppPacktrack\Mail\PaketReminderMail;
use Hwkdo\IntranetAppPacktrack\Models\IntranetAppPacktrackSettings;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class PacktrackReminderCommand extends Command
{
    protected $signature = 'packtrack:reminder';

    protected $description = 'Sendet Erinnerungsmails für nicht abgeholte Pakete';

    public function handle(): int
    {
        $settings = IntranetAppPacktrackSettings::current()?->settings;
        $reminderNachTagen = $settings?->reminderNachTagen ?? 3;

        $this->info("Prüfe Pakete mit Erinnerung nach {$reminderNachTagen} Tagen...");

        $nichtAbgeholt = Paket::nichtAbgeholt()
            ->with('empfaenger')
            ->get();

        $heute = Carbon::today();
        $erinnert = 0;

        foreach ($nichtAbgeholt as $paket) {
            $alter = (int) $paket->created_at->diff($heute)->days;

            if ($alter < $reminderNachTagen) {
                continue;
            }

            if ($alter % $reminderNachTagen !== 0) {
                continue;
            }

            $empfaenger = $paket->empfaenger;
            $anzahlOffen = Paket::nichtAbgeholtFuerUser($empfaenger->id)->count();

            $this->line("Sende Reminder für Paket #{$paket->id} an {$empfaenger->email}");

            Mail::to($empfaenger->email)
                ->queue(new PaketReminderMail($empfaenger, $anzahlOffen));

            $erinnert++;
        }

        $this->info("{$erinnert} Reminder versendet.");

        return self::SUCCESS;
    }
}
