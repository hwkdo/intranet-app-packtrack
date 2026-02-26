<?php

use App\Models\User;
use Flux\Flux;
use Hwkdo\IntranetAppPacktrack\Mail\PaketAusgabeMail;
use Hwkdo\IntranetAppPacktrack\Models\Abholung;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('PackTrack – Paketausgabe')] class extends Component {
    public int $abholerId = 0;
    public string $barcode = '';
    public bool $zeigUnterschriftModal = false;
    public string $unterschrift = '';

    /**
     * @var array<int, array{nr: string, paket_id: int|null, empfaenger: string, packetdienst: string, status: string}>
     */
    public array $gescannteNummern = [];

    public bool $ausgegeben = false;

    #[Computed]
    public function benutzer(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()->aktiv()->orderBy('nachname')->orderBy('vorname')->get();
    }

    #[Computed]
    public function abholerName(): string
    {
        if ($this->abholerId === 0) {
            return '';
        }

        return $this->benutzer->firstWhere('id', $this->abholerId)?->name ?? '';
    }

    public function scannen(): void
    {
        $nr = trim($this->barcode);
        $this->barcode = '';

        if ($nr === '') {
            return;
        }

        // Doppelt-Scan verhindern
        foreach ($this->gescannteNummern as $eintrag) {
            if ($eintrag['nr'] === $nr) {
                Flux::toast(text: "Barcode \"{$nr}\" wurde bereits gescannt.", variant: 'warning');
                $this->dispatch('focus-barcode');
                return;
            }
        }

        $paket = Paket::with(['empfaenger', 'packetdienst', 'abholung'])
            ->where('nr', $nr)
            ->first();

        if (!$paket) {
            $this->gescannteNummern[] = [
                'nr' => $nr,
                'paket_id' => null,
                'empfaenger' => '',
                'packetdienst' => '',
                'status' => 'nicht_gefunden',
            ];
            $this->dispatch('focus-barcode');
            return;
        }

        if ($paket->istAbgeholt()) {
            $this->gescannteNummern[] = [
                'nr' => $nr,
                'paket_id' => $paket->id,
                'empfaenger' => $paket->empfaenger->name,
                'packetdienst' => $paket->packetdienst->name,
                'status' => 'bereits_abgeholt',
            ];
            $this->dispatch('focus-barcode');
            return;
        }

        $this->gescannteNummern[] = [
            'nr' => $nr,
            'paket_id' => $paket->id,
            'empfaenger' => $paket->empfaenger->name,
            'packetdienst' => $paket->packetdienst->name,
            'status' => 'bereit',
        ];

        $this->dispatch('focus-barcode');
    }

    public function removeNummer(int $index): void
    {
        unset($this->gescannteNummern[$index]);
        $this->gescannteNummern = array_values($this->gescannteNummern);
    }

    public function ausgabeVorbereiten(): void
    {
        $this->validate([
            'abholerId' => 'required|integer|min:1|exists:users,id',
        ], [
            'abholerId.required' => 'Bitte den Abholer auswählen.',
            'abholerId.min' => 'Bitte den Abholer auswählen.',
        ]);

        $bereite = collect($this->gescannteNummern)->filter(fn ($e) => $e['status'] === 'bereit');

        if ($bereite->isEmpty()) {
            Flux::toast(text: 'Keine Pakete zur Ausgabe bereit.', variant: 'warning');

            return;
        }

        $this->zeigUnterschriftModal = true;
    }

    #[On('signature-confirmed')]
    public function unterschriftGespeichert(string $img_src, string $base64, array $checkboxes): void
    {
        $this->unterschrift = $img_src;
        $this->zeigUnterschriftModal = false;
        $this->ausgeben();
    }

    public function ausgeben(): void
    {
        $bereite = collect($this->gescannteNummern)->filter(fn ($e) => $e['status'] === 'bereit');

        if ($bereite->isEmpty()) {
            Flux::toast(text: 'Keine Pakete zur Ausgabe bereit.', variant: 'warning');

            return;
        }

        $abholer = User::find($this->abholerId);
        $verarbeiteteEmpfaenger = [];

        foreach ($bereite as $index => $eintrag) {
            $paket = Paket::with('empfaenger')->find($eintrag['paket_id']);
            if (!$paket) {
                continue;
            }

            Abholung::create([
                'paket_id' => $paket->id,
                'ausgeber_id' => auth()->id(),
                'abholer_id' => $this->abholerId,
                'unterschrift' => $this->unterschrift ?: null,
            ]);

            $this->gescannteNummern[$index]['status'] = 'ausgegeben';

            // E-Mail nur wenn Abholer != Empfänger
            if ($paket->empfaenger_id !== $this->abholerId) {
                $empfaengerId = $paket->empfaenger_id;
                $verarbeiteteEmpfaenger[$empfaengerId] = ($verarbeiteteEmpfaenger[$empfaengerId] ?? 0) + 1;
            }
        }

        foreach ($verarbeiteteEmpfaenger as $empfaengerId => $anzahl) {
            $empfaenger = User::find($empfaengerId);
            Mail::to($empfaenger->email)->queue(new PaketAusgabeMail(
                empfaenger: $empfaenger,
                abholer: $abholer,
                anzahl: $anzahl,
            ));
        }

        $this->ausgegeben = true;
        Flux::toast(
            text: $bereite->count() . ' ' . ($bereite->count() === 1 ? 'Paket' : 'Pakete') . ' ausgegeben.',
            variant: 'success',
        );
    }

    public function neueAusgabe(): void
    {
        $this->reset(['gescannteNummern', 'barcode', 'ausgegeben', 'unterschrift', 'zeigUnterschriftModal']);
        $this->dispatch('focus-barcode');
    }
} ?>
<div>
<x-intranet-app-packtrack::packtrack-layout heading="PackTrack" subheading="Paketausgabe">
    <div
        x-data="{}"
        @focus-barcode.window="$nextTick(() => { const el = document.getElementById('barcode-input'); if (el) el.focus(); })"
    >
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Linke Seite: Abholer-Auswahl --}}
            <flux:card class="lg:col-span-1">
                <flux:heading size="md" class="mb-4">Abholer</flux:heading>
                <flux:field>
                    <flux:label>Abholer <flux:badge size="sm" color="red" class="ml-1">Pflicht</flux:badge></flux:label>
                    <flux:select
                        variant="listbox"
                        searchable
                        wire:model.live="abholerId"
                        placeholder="Abholer wählen..."
                        :disabled="$ausgegeben"
                    >
                        @foreach($this->benutzer as $user)
                            <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="abholerId" />
                </flux:field>

                @if($abholerId > 0 && !$ausgegeben)
                    <div class="mt-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3">
                        <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                            <flux:icon name="information-circle" class="size-4 inline mr-1" />
                            Barcode-Feld ist aktiv. Pakete scannen.
                        </flux:text>
                    </div>
                @endif

                @if(!$ausgegeben)
                    <div class="mt-4 space-y-2">
                        @php
                            $bereitAnzahl = collect($gescannteNummern)->filter(fn($e) => $e['status'] === 'bereit')->count();
                        @endphp
                        <flux:button
                            wire:click="ausgabeVorbereiten"
                            wire:loading.attr="disabled"
                            variant="primary"
                            icon="arrow-up-tray"
                            class="w-full"
                            :disabled="$bereitAnzahl === 0 || $abholerId === 0"
                        >
                            <span wire:loading.remove wire:target="ausgabeVorbereiten">
                                {{ $bereitAnzahl }} {{ $bereitAnzahl === 1 ? 'Paket' : 'Pakete' }} ausgeben
                            </span>
                            <span wire:loading wire:target="ausgabeVorbereiten">Bitte warten ...</span>
                        </flux:button>
                    </div>
                @else
                    <flux:button wire:click="neueAusgabe" variant="primary" icon="arrow-path" class="w-full mt-4">
                        Neue Ausgabe starten
                    </flux:button>
                @endif
            </flux:card>

            {{-- Rechte Seite: Scanner --}}
            <flux:card class="lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="md">Barcode-Eingabe</flux:heading>
                    @if(!$ausgegeben)
                        <flux:badge color="blue" size="sm">
                            {{ collect($gescannteNummern)->filter(fn($e) => $e['status'] === 'bereit')->count() }} bereit
                        </flux:badge>
                    @endif
                </div>

                @if(!$ausgegeben)
                    <div class="mb-4">
                        <flux:input
                            wire:model="barcode"
                            @keydown.enter.prevent="$wire.scannen()"
                            id="barcode-input"
                            placeholder="Barcode scannen oder eingeben ..."
                            class="font-mono"
                            autofocus
                            :disabled="$abholerId === 0"
                        />
                        @if($abholerId === 0)
                            <flux:text size="sm" class="text-zinc-500 mt-1">Bitte zuerst einen Abholer auswählen.</flux:text>
                        @endif
                    </div>
                @endif

                @if(!empty($gescannteNummern))
                    <div class="space-y-2">
                        @foreach($gescannteNummern as $i => $eintrag)
                            <div
                                wire:key="scan-{{ $i }}"
                                class="flex items-center gap-3 rounded-lg border px-4 py-2.5
                                    {{ $eintrag['status'] === 'bereit' ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : '' }}
                                    {{ $eintrag['status'] === 'ausgegeben' ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : '' }}
                                    {{ $eintrag['status'] === 'nicht_gefunden' ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' : '' }}
                                    {{ $eintrag['status'] === 'bereits_abgeholt' ? 'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50' : '' }}
                                "
                            >
                                @if($eintrag['status'] === 'bereit')
                                    <flux:icon name="check-circle" class="size-5 text-green-600 dark:text-green-400 shrink-0" />
                                @elseif($eintrag['status'] === 'ausgegeben')
                                    <flux:icon name="check-badge" class="size-5 text-green-700 dark:text-green-300 shrink-0" />
                                @elseif($eintrag['status'] === 'nicht_gefunden')
                                    <flux:icon name="x-circle" class="size-5 text-red-500 shrink-0" />
                                @elseif($eintrag['status'] === 'bereits_abgeholt')
                                    <flux:icon name="archive-box" class="size-5 text-zinc-400 shrink-0" />
                                @endif

                                <div class="flex-1 min-w-0">
                                    <div class="font-mono font-medium text-sm">{{ $eintrag['nr'] }}</div>
                                    @if($eintrag['empfaenger'])
                                        <div class="text-xs text-zinc-500">{{ $eintrag['empfaenger'] }} · {{ $eintrag['packetdienst'] }}</div>
                                    @endif
                                </div>

                                @if($eintrag['status'] === 'bereit')
                                    <flux:badge color="green" size="sm" class="shrink-0">Bereit</flux:badge>
                                @elseif($eintrag['status'] === 'ausgegeben')
                                    <flux:badge color="green" size="sm" class="shrink-0">Ausgegeben</flux:badge>
                                @elseif($eintrag['status'] === 'nicht_gefunden')
                                    <flux:badge color="red" size="sm" class="shrink-0">Nicht gefunden</flux:badge>
                                @elseif($eintrag['status'] === 'bereits_abgeholt')
                                    <flux:badge size="sm" class="shrink-0">Bereits abgeholt</flux:badge>
                                @endif

                                @if(!$ausgegeben && in_array($eintrag['status'], ['bereit', 'nicht_gefunden', 'bereits_abgeholt']))
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
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-zinc-400">
                        <flux:icon name="archive-box" class="size-12 mb-3" />
                        <flux:text>Noch keine Barcodes gescannt</flux:text>
                    </div>
                @endif
            </flux:card>
        </div>
    </div>
    <flux:modal wire:model="zeigUnterschriftModal" class="max-w-xl w-full">
        <flux:heading size="lg" class="mb-1">Unterschrift erforderlich</flux:heading>
        <flux:text class="mb-4 text-zinc-500">
            Der Abholer muss den Empfang der Pakete durch Unterschrift bestätigen.
        </flux:text>
        @if($zeigUnterschriftModal)
            <livewire:signopad.signpad
                :textOben="'Paketausgabe – Ich bestätige den Empfang der Pakete.'"
                :textUnten="$this->abholerName"
            />
        @endif
    </flux:modal>
</x-intranet-app-packtrack::packtrack-layout>
</div>