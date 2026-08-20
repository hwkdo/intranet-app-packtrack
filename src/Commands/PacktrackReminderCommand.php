<?php

namespace Hwkdo\IntranetAppPacktrack\Commands;

use Carbon\Carbon;
use Hwkdo\IntranetAppPacktrack\Models\IntranetAppPacktrackSettings;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Hwkdo\IntranetAppPacktrack\Notifications\PaketReminderNotification;
use Illuminate\Console\Command;

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

            $empfaenger->notify(new PaketReminderNotification($empfaenger, $anzahlOffen));

            $erinnert++;
        }

        $this->info("{$erinnert} Reminder versendet.");

        return self::SUCCESS;
    }
}
