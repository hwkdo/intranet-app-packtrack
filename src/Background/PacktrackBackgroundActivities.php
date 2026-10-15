<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppPacktrack\Background;

use Hwkdo\IntranetAppBase\Data\BackgroundActivityDefinition;
use Hwkdo\IntranetAppPacktrack\IntranetAppPacktrack;
use Hwkdo\IntranetAppPacktrack\Mail\PaketAusgabeMail;
use Hwkdo\IntranetAppPacktrack\Mail\PaketEmpfangMail;
use Hwkdo\IntranetAppPacktrack\Mail\PaketReminderMail;

final class PacktrackBackgroundActivities
{
    /**
     * @return list<BackgroundActivityDefinition>
     */
    public static function definitions(): array
    {
        $id = IntranetAppPacktrack::identifier();
        $name = IntranetAppPacktrack::app_name();

        return [
            BackgroundActivityDefinition::mail(key: 'packtrack.mail-empfang', label: 'Mail: Paket empfangen', appIdentifier: $id, appName: $name, mailable: PaketEmpfangMail::class, sort: 10),
            BackgroundActivityDefinition::mail(key: 'packtrack.mail-ausgabe', label: 'Mail: Paket ausgegeben', appIdentifier: $id, appName: $name, mailable: PaketAusgabeMail::class, sort: 20),
            BackgroundActivityDefinition::mail(key: 'packtrack.mail-reminder', label: 'Mail: Paket-Erinnerung', appIdentifier: $id, appName: $name, mailable: PaketReminderMail::class, sort: 30),
        ];
    }
}
