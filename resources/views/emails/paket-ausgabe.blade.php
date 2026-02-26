<x-mail::message>
# Ihre Pakete wurden abgeholt

Hallo {{ $empfaenger->vorname }},

**{{ $anzahl }} {{ $anzahl === 1 ? 'Paket' : 'Pakete' }}** {{ $anzahl === 1 ? 'wurde' : 'wurden' }} von **{{ $abholer->name }}** für Sie abgeholt.

@if($anzahlGesamt > 0)
Es {{ $anzahlGesamt === 1 ? 'liegt noch **1 weiteres Paket**' : 'liegen noch **' . $anzahlGesamt . ' weitere Pakete**' }} für Sie zur Abholung bereit.

**Öffnungszeiten:** {{ $oeffnungszeiten }}
@endif

Viele Grüße
</x-mail::message>
