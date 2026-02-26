<x-mail::message>
# Neue Pakete an der Poststelle

Hallo {{ $empfaenger->vorname }},

es {{ $anzahl === 1 ? 'ist' : 'sind' }} **{{ $anzahl }} {{ $anzahl === 1 ? 'Paket' : 'Pakete' }}** ({{ $packetdienst }}) für Sie an der Poststelle eingegangen.

@if($lieferant)
**Absender:** {{ $lieferant }}
@endif

@if($bemerkung)
**Bemerkung:** {{ $bemerkung }}
@endif

@if($anzahlGesamt > $anzahl)
Insgesamt liegen **{{ $anzahlGesamt }} Pakete** für Sie zur Abholung bereit.
@endif

**Öffnungszeiten:** {{ $oeffnungszeiten }}

<x-mail::button :url="config('app.url')">
Zum Intranet
</x-mail::button>

Viele Grüße
</x-mail::message>
