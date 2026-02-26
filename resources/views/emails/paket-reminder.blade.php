<x-mail::message>
# Erinnerung: Pakete an der Poststelle

Hallo {{ $empfaenger->vorname }},

es {{ $anzahlOffen === 1 ? 'liegt noch **1 Paket**' : 'liegen noch **' . $anzahlOffen . ' Pakete**' }} für Sie an der Poststelle bereit – bitte holen Sie {{ $anzahlOffen === 1 ? 'es' : 'diese' }} ab.

**Öffnungszeiten:** {{ $oeffnungszeiten }}

<x-mail::button :url="config('app.url')">
Zum Intranet
</x-mail::button>

Viele Grüße
</x-mail::message>
