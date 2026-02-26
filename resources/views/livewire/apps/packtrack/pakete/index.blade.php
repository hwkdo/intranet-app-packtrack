<?php

use Flux\Flux;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] #[Title('PackTrack – Pakete')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filter = 'offen';

    /** Für das Lösch-Modal */
    public ?int $loeschenPaketId = null;
    public string $loeschenKommentar = '';

    /** Für das Unterschrifts-Modal */
    public ?string $unterschriftData = null;
    public bool $zeigUnterschriftModal = false;

    #[Computed]
    public function pakete(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Paket::query()
            ->with(['empfaenger', 'annehmer', 'packetdienst', 'abholung.abholer'])
            ->when($this->search, function ($q) {
                $q->where('nr', 'like', "%{$this->search}%")
                    ->orWhereHas('empfaenger', fn ($q) => $q->where('vorname', 'like', "%{$this->search}%")->orWhere('nachname', 'like', "%{$this->search}%"))
                    ->orWhere('lieferant', 'like', "%{$this->search}%");
            })
            ->when($this->filter === 'offen', fn ($q) => $q->whereDoesntHave('abholung'))
            ->when($this->filter === 'abgeholt', fn ($q) => $q->whereHas('abholung'))
            ->latest()
            ->paginate(20);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function unterschriftAnzeigen(int $paketId): void
    {
        $paket = Paket::with('abholung')->findOrFail($paketId);
        $this->unterschriftData = $paket->abholung?->unterschrift;
        $this->zeigUnterschriftModal = true;
    }

    public function loeschenOeffnen(int $paketId): void
    {
        $this->loeschenPaketId = $paketId;
        $this->loeschenKommentar = '';
        $this->dispatch('open-modal', name: 'loeschen-modal');
    }

    public function loeschen(): void
    {
        $this->authorize('manage-app-packtrack');

        $paket = Paket::findOrFail($this->loeschenPaketId);
        $paket->update([
            'geloescht_kommentar' => $this->loeschenKommentar ?: null,
            'geloescht_von' => auth()->id(),
        ]);
        $paket->delete();

        $this->reset(['loeschenPaketId', 'loeschenKommentar']);
        $this->dispatch('close-modal', name: 'loeschen-modal');
        unset($this->pakete);

        Flux::toast(text: 'Paket wurde gelöscht.', variant: 'success');
    }
} ?>
<div>
<x-intranet-app-packtrack::packtrack-layout heading="PackTrack" subheading="Alle Pakete">
    <div class="space-y-4">
        {{-- Filter-Leiste --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Tracking-Nr., Empfänger, Lieferant ..."
                icon="magnifying-glass"
                class="w-full max-w-sm"
            />

            <flux:select wire:model.live="filter" class="w-40">
                <flux:select.option value="offen">Offen</flux:select.option>
                <flux:select.option value="abgeholt">Abgeholt</flux:select.option>
                <flux:select.option value="alle">Alle</flux:select.option>
            </flux:select>
        </div>

        {{-- Tabelle --}}
        <flux:table :paginate="$this->pakete">
            <flux:table.columns>
                <flux:table.column>Tracking-Nr.</flux:table.column>
                <flux:table.column>Empfänger</flux:table.column>
                <flux:table.column>Dienst</flux:table.column>
                <flux:table.column>Lieferant</flux:table.column>
                <flux:table.column>Angenommen</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($this->pakete as $paket)
                    <flux:table.row wire:key="paket-{{ $paket->id }}">
                        <flux:table.cell class="font-mono text-sm">{{ $paket->nr }}</flux:table.cell>
                        <flux:table.cell>{{ $paket->empfaenger->name }}</flux:table.cell>
                        <flux:table.cell>{{ $paket->packetdienst->name }}</flux:table.cell>
                        <flux:table.cell>{{ $paket->lieferant ?? '–' }}</flux:table.cell>
                        <flux:table.cell>
                            <div>{{ $paket->created_at->format('d.m.Y') }}</div>
                            <div class="text-xs text-zinc-500">{{ $paket->annehmer->name }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($paket->abholung)
                                <flux:badge color="green" size="sm">
                                    Abgeholt {{ $paket->abholung->created_at->format('d.m.Y') }}
                                </flux:badge>
                                <div class="text-xs text-zinc-500 mt-0.5">von {{ $paket->abholung->abholer->name }}</div>
                            @else
                                <flux:badge color="yellow" size="sm">Offen</flux:badge>
                                <div class="text-xs text-zinc-500 mt-0.5">seit {{ $paket->created_at->diffForHumans() }}</div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                @if($paket->abholung?->unterschrift)
                                    <flux:button
                                        wire:click="unterschriftAnzeigen({{ $paket->id }})"
                                        wire:target="unterschriftAnzeigen({{ $paket->id }})"
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        class="text-zinc-400 hover:text-blue-500"
                                    />
                                @endif
                                @can('manage-app-packtrack')
                                    <flux:button
                                        wire:click="loeschenOeffnen({{ $paket->id }})"
                                        wire:target="loeschenOeffnen({{ $paket->id }})"
                                        :loading="false"
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        class="text-zinc-400 hover:text-red-500"
                                    />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8 text-zinc-500">
                            Keine Pakete gefunden.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Unterschrifts-Modal --}}
    <flux:modal wire:model="zeigUnterschriftModal" class="max-w-lg w-full">
        <flux:heading size="lg" class="mb-4">Unterschrift des Abholers</flux:heading>
        @if($unterschriftData)
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2">
                <img src="{{ $unterschriftData }}" alt="Unterschrift" class="max-w-full h-auto" />
            </div>
        @else
            <flux:text class="text-zinc-500">Keine Unterschrift vorhanden.</flux:text>
        @endif
        <div class="mt-4 flex justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">Schließen</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>

    {{-- Lösch-Modal --}}
    <flux:modal name="loeschen-modal" class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Paket löschen</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Das Paket wird als gelöscht markiert (Soft-Delete).</flux:text>
            </div>

            <flux:field>
                <flux:label>Grund / Kommentar (optional)</flux:label>
                <flux:textarea wire:model="loeschenKommentar" rows="2" placeholder="z.B. Fehleingabe, falsches Paket ..." />
            </flux:field>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Abbrechen</flux:button>
                </flux:modal.close>
                <flux:button wire:click="loeschen" variant="danger" icon="trash">
                    Löschen
                </flux:button>
            </div>
        </div>
    </flux:modal>
</x-intranet-app-packtrack::packtrack-layout>
</div>