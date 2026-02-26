<?php

use App\Models\User;
use Flux\Flux;
use Hwkdo\BueLaravel\Facades\BueLaravel;
use Hwkdo\IntranetAppPacktrack\Mail\PaketEmpfangMail;
use Hwkdo\IntranetAppPacktrack\Models\Packetdienst;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('PackTrack – Paketannahme')] class extends Component {
    public ?int $empfaengerId = null;
    public ?int $packetdienstId = null;
    public string $lieferant = '';
    public string $lieferantSuche = '';
    public string $bemerkung = '';

    /** @var array<int, array{nr: string, status: string}> */
    public array $nummern = [
        ['nr' => '', 'status' => ''],
    ];

    /** @var array<int, array{nr: string, status: string}> */
    public array $ergebnis = [];

    public bool $gespeichert = false;

    #[Computed]
    public function benutzer(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()->aktiv()->orderBy('nachname')->orderBy('vorname')->get();
    }

    #[Computed]
    public function packetdienste(): \Illuminate\Database\Eloquent\Collection
    {
        return Packetdienst::orderBy('name')->get();
    }

    #[Computed]
    public function lieferantenListe(): \Illuminate\Support\Collection
    {
        return BueLaravel::getLieferanten($this->lieferantSuche);
    }

    public function updated(string $property): void
    {
        if (!str_starts_with($property, 'nummern.')) {
            return;
        }

        preg_match('/nummern\.(\d+)\.nr/', $property, $matches);
        if (!isset($matches[1])) {
            return;
        }

        $index = (int) $matches[1];
        $nr = trim($this->nummern[$index]['nr'] ?? '');

        if ($nr === '') {
            $this->nummern[$index]['status'] = '';
            return;
        }

        $this->nummern[$index]['status'] = Paket::withTrashed()->where('nr', $nr)->exists()
            ? 'duplikat'
            : 'ok';
    }

    public function addNummer(): void
    {
        $this->nummern[] = ['nr' => '', 'status' => ''];
        $this->dispatch('focus-nr-input', index: count($this->nummern) - 1);
    }

    public function removeNummer(int $index): void
    {
        unset($this->nummern[$index]);
        $this->nummern = array_values($this->nummern);

        if (empty($this->nummern)) {
            $this->nummern = [['nr' => '', 'status' => '']];
        }
    }

    public function speichern(): void
    {
        $this->validate([
            'empfaengerId' => 'required|integer|min:1|exists:users,id',
            'packetdienstId' => 'required|integer|min:1|exists:packtrack_packetdienste,id',
        ], [
            'empfaengerId.required' => 'Bitte einen Empfänger auswählen.',
            'empfaengerId.min' => 'Bitte einen Empfänger auswählen.',
            'packetdienstId.required' => 'Bitte einen Packetdienst auswählen.',
            'packetdienstId.min' => 'Bitte einen Packetdienst auswählen.',
        ]);

        $gespeicherteAnzahl = 0;
        $duplikateAnzahl = 0;

        $lieferantName = null;
        $lieferantNr = null;
        if ($this->lieferant !== '') {
            $lieferantEntry = BueLaravel::getLieferantByNummer($this->lieferant);
            $lieferantName = $lieferantEntry?->lieferantenname;
            $lieferantNr = $this->lieferant;
        }

        foreach ($this->nummern as &$eintrag) {
            $nr = trim($eintrag['nr']);
            if ($nr === '') {
                continue;
            }

            if (Paket::withTrashed()->where('nr', $nr)->exists()) {
                $duplikateAnzahl++;
                $eintrag['status'] = 'duplikat';
                continue;
            }

            Paket::create([
                'nr' => $nr,
                'empfaenger_id' => $this->empfaengerId,
                'annehmer_id' => auth()->id(),
                'packetdienst_id' => $this->packetdienstId,
                'lieferant' => $lieferantName,
                'lieferant_nr' => $lieferantNr,
                'bemerkung' => $this->bemerkung ?: null,
            ]);

            $eintrag['status'] = 'gespeichert';
            $gespeicherteAnzahl++;
        }
        unset($eintrag);

        if ($gespeicherteAnzahl > 0) {
            $empfaenger = User::find($this->empfaengerId);
            $packetdienst = Packetdienst::find($this->packetdienstId);

            Mail::to($empfaenger->email)->queue(new PaketEmpfangMail(
                empfaenger: $empfaenger,
                packetdienst: $packetdienst->name,
                anzahl: $gespeicherteAnzahl,
                lieferant: $this->lieferant ?: null,
                bemerkung: $this->bemerkung ?: null,
            ));

            Flux::toast(
                text: "{$gespeicherteAnzahl} " . ($gespeicherteAnzahl === 1 ? 'Paket' : 'Pakete') . ' gespeichert' . ($duplikateAnzahl > 0 ? ", {$duplikateAnzahl} Duplikat(e) übersprungen" : '') . '.',
                variant: 'success',
            );
        } else {
            Flux::toast(text: 'Keine neuen Pakete gespeichert.', variant: 'warning');
        }

        $this->ergebnis = array_values(array_filter($this->nummern, fn ($e) => trim($e['nr']) !== ''));
        $this->gespeichert = true;
    }

    public function neueAnnahme(): void
    {
        $this->reset(['nummern', 'ergebnis', 'gespeichert', 'bemerkung', 'lieferant', 'lieferantSuche']);
        $this->nummern = [['nr' => '', 'status' => '']];
        $this->dispatch('focus-first-input');
    }
} ?>

<div>
<x-intranet-app-packtrack::packtrack-layout heading="PackTrack" subheading="Paketannahme">
    <div
        x-data="{}"
        @focus-first-input.window="$nextTick(() => { const first = $el.querySelector('input[autofocus]') ?? $el.querySelector('input'); if (first) first.focus(); })"
        @focus-nr-input.window="$nextTick(() => { const el = document.getElementById('nr-input-' + $event.detail.index); if (el) el.focus(); })"
    >
        @if($gespeichert)
            {{-- Ergebnis-Ansicht --}}
            <flux:card class="mb-4">
                <flux:heading size="lg" class="mb-4">Annahme abgeschlossen</flux:heading>
                <div class="space-y-2 mb-6">
                    @foreach($ergebnis as $eintrag)
                        <div class="flex items-center gap-3 rounded-lg border px-4 py-2 {{ $eintrag['status'] === 'gespeichert' ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20' }}">
                            @if($eintrag['status'] === 'gespeichert')
                                <flux:icon name="check-circle" class="size-5 text-green-600 dark:text-green-400 shrink-0" />
                                <span class="font-mono font-medium">{{ $eintrag['nr'] }}</span>
                                <flux:badge color="green" size="sm">Gespeichert</flux:badge>
                            @else
                                <flux:icon name="exclamation-triangle" class="size-5 text-amber-600 dark:text-amber-400 shrink-0" />
                                <span class="font-mono font-medium">{{ $eintrag['nr'] }}</span>
                                <flux:badge color="yellow" size="sm">Duplikat</flux:badge>
                            @endif
                        </div>
                    @endforeach
                </div>
                <flux:button wire:click="neueAnnahme" variant="primary" icon="plus" autofocus>
                    Neue Annahme starten
                </flux:button>
            </flux:card>
        @else
            {{-- Eingabe-Formular --}}
            <div class="grid gap-4 lg:grid-cols-3">
                {{-- Stammdaten --}}
                <flux:card class="lg:col-span-1">
                    <flux:heading size="md" class="mb-4">Paketdaten</flux:heading>
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Empfänger <flux:badge size="sm" color="red" class="ml-1">Pflicht</flux:badge></flux:label>
                            <flux:select variant="listbox" searchable wire:model.live="empfaengerId" placeholder="Empfänger wählen...">
                                @foreach($this->benutzer as $user)
                                    <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="empfaengerId" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Packetdienst <flux:badge size="sm" color="red" class="ml-1">Pflicht</flux:badge></flux:label>
                            <flux:select variant="listbox" searchable wire:model.live="packetdienstId" placeholder="Dienst wählen...">
                                @foreach($this->packetdienste as $dienst)
                                    <flux:select.option value="{{ $dienst->id }}">{{ $dienst->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="packetdienstId" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Lieferant / Absender</flux:label>
                            <flux:select variant="listbox" searchable :filter="false" wire:model="lieferant" placeholder="Lieferant wählen ...">
                                <x-slot name="search">
                                    <flux:select.search wire:model.live.debounce.300ms="lieferantSuche" placeholder="Lieferant suchen ..." />
                                </x-slot>
                                @foreach($this->lieferantenListe as $lieferantOption)
                                    <flux:select.option value="{{ $lieferantOption->lieferantennummer }}" wire:key="{{ $lieferantOption->lieferantennummer }}">{{ $lieferantOption->lieferantenname }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>Bemerkung</flux:label>
                            <flux:textarea wire:model="bemerkung" rows="2" placeholder="Optionale Bemerkung ..." />
                        </flux:field>
                    </div>
                </flux:card>

                {{-- Barcode-Eingabe --}}
                <flux:card class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="md">Tracking-Nummern</flux:heading>
                        <flux:badge color="blue" size="sm">
                            {{ collect($nummern)->filter(fn($e) => $e['status'] === 'ok')->count() }} bereit
                        </flux:badge>
                    </div>

                    <div class="space-y-2 mb-4">
                        @foreach($nummern as $i => $eintrag)
                            <div wire:key="nr-{{ $i }}" class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <flux:input
                                        id="nr-input-{{ $i }}"
                                        wire:model.live="nummern.{{ $i }}.nr"
                                        placeholder="Barcode scannen oder eingeben ..."
                                        class="font-mono"
                                        @keydown.enter.prevent="$wire.addNummer()"
                                        {{ $i === 0 ? 'autofocus' : '' }}
                                    />
                                    @if($eintrag['status'] === 'ok')
                                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                            <flux:icon name="check-circle" class="size-4 text-green-500" />
                                        </span>
                                    @elseif($eintrag['status'] === 'duplikat')
                                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                            <flux:icon name="exclamation-circle" class="size-4 text-amber-500" />
                                        </span>
                                    @endif
                                </div>

                                @if($eintrag['status'] === 'duplikat')
                                    <flux:badge color="yellow" size="sm" class="shrink-0">Duplikat</flux:badge>
                                @endif

                                @if(count($nummern) > 1)
                                    <flux:button
                                        wire:click="removeNummer({{ $i }})"
                                        size="sm"
                                        variant="ghost"
                                        icon="x-mark"
                                        class="shrink-0"
                                    />
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <flux:button wire:click="addNummer" variant="ghost" icon="plus" size="sm">
                        Zeile hinzufügen
                    </flux:button>
                </flux:card>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <flux:button
                    wire:click="speichern"
                    wire:loading.attr="disabled"
                    variant="primary"
                    icon="inbox-arrow-down"
                >
                    <span wire:loading.remove wire:target="speichern">Pakete speichern</span>
                    <span wire:loading wire:target="speichern">Wird gespeichert ...</span>
                </flux:button>
                <flux:text size="sm" class="text-zinc-500">
                    @php $bereit = collect($nummern)->filter(fn($e) => trim($e['nr']) !== '' && $e['status'] !== 'duplikat')->count(); @endphp
                    {{ $bereit }} {{ $bereit === 1 ? 'Paket' : 'Pakete' }} werden gespeichert
                </flux:text>
            </div>
        @endif
    </div>
</x-intranet-app-packtrack::packtrack-layout>
</div>