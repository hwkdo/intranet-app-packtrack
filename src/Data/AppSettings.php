<?php

namespace Hwkdo\IntranetAppPacktrack\Data;

use Hwkdo\IntranetAppBase\Data\Attributes\Description;
use Hwkdo\IntranetAppBase\Data\BaseAppSettings;

class AppSettings extends BaseAppSettings
{
    public function __construct(
        #[Description('Anzahl Tage ohne Abholung bis eine Erinnerungsmail gesendet wird')]
        public int $reminderNachTagen = 3,

        #[Description('Öffnungszeiten der Poststelle (wird in E-Mails angezeigt)')]
        public string $oeffnungszeiten = 'Mo–Fr 8:00–16:00 Uhr',
    ) {}
}
