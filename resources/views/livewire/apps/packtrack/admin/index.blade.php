<?php

use Flux\Flux;
use Hwkdo\IntranetAppPacktrack\Models\Abholung;
use Hwkdo\IntranetAppPacktrack\Models\Packetdienst;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('PackTrack – Admin')] class extends Component {
    public string $activeTab = 'einstellungen';

    /** Packetdienste-CRUD */
    public string $neuerDienstName = '';
    public ?int $editDienstId = null;
    public string $editDienstName = '';

    #[Computed]
    public function packetdienste(): \Illuminate\Database\Eloquent\Collection
    {
        return Packetdienst::withCount('pakete')->orderBy('name')->get();
    }

    #[Computed]
    public function statistiken(): array
    {
        return [
            'gesamt' => Paket::withTrashed()->count(),
            'offen' => Paket::nichtAbgeholt()->count(),
            'abgeholt' => Paket::whereHas('abholung')->count(),
            'geloescht' => Paket::onlyTrashed()->count(),
            'heuteAngenommen' => Paket::whereDate('created_at', today())->count(),
            'heuteAbgeholt' => Abholung::whereDate('created_at', today())->count(),
            'dieseWocheAngenommen' => Paket::where('created_at', '>=', now()->startOfWeek())->count(),
            'altestesOffen' => Paket::nichtAbgeholt()->oldest()->value('created_at'),
        ];
    }

    public function dienstSpeichern(): void
    {
        $this->validate([
            'neuerDienstName' => 'required|string|max:100|unique:packtrack_packetdienste,name',
        ], [
            'neuerDienstName.required' => 'Bitte einen Namen eingeben.',
            'neuerDienstName.unique' => 'Dieser Packetdienst existiert bereits.',
        ]);

        Packetdienst::create(['name' => trim($this->neuerDienstName)]);
        $this->reset('neuerDienstName');
        unset($this->packetdienste);
        Flux::toast(text: 'Packetdienst gespeichert.', variant: 'success');
    }

    public function dienstBearbeitenOeffnen(int $id): void
    {
        $dienst = Packetdienst::findOrFail($id);
        $this->editDienstId = $id;
        $this->editDienstName = $dienst->name;
    }

    public function dienstBearbeitenSpeichern(): void
    {
        $this->validate([
            'editDienstName' => 'required|string|max:100|unique:packtrack_packetdienste,name,' . $this->editDienstId,
        ], [
            'editDienstName.required' => 'Bitte einen Namen eingeben.',
            'editDienstName.unique' => 'Dieser Name ist bereits vergeben.',
        ]);

        Packetdienst::findOrFail($this->editDienstId)->update(['name' => trim($this->editDienstName)]);
        $this->reset(['editDienstId', 'editDienstName']);
        unset($this->packetdienste);
        Flux::toast(text: 'Packetdienst aktualisiert.', variant: 'success');
    }

    public function dienstLoeschen(int $id): void
    {
        $dienst = Packetdienst::withCount('pakete')->findOrFail($id);

        if ($dienst->pakete_count > 0) {
            Flux::toast(text: 'Packetdienst kann nicht gelöscht werden – es sind noch Pakete zugeordnet.', variant: 'danger');
            return;
        }

        $dienst->delete();
        unset($this->packetdienste);
        Flux::toast(text: 'Packetdienst gelöscht.', variant: 'success');
    }
} ?>

<x-intranet-app-packtrack::packtrack-layout heading="PackTrack" subheading="Administration">
    <flux:tab.group>
        <flux:tabs wire:model="activeTab">
            <flux:tab name="hintergrundbild" icon="photo">Hintergrundbild</flux:tab>
            <flux:tab name="einstellungen" icon="cog-6-tooth">Einstellungen</flux:tab>
            <flux:tab name="statistiken" icon="chart-bar">Statistiken</flux:tab>
            <flux:tab name="packetdienste" icon="truck">Packetdienste</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="hintergrundbild">
            <div style="min-height: 400px;">
                @livewire('intranet-app-base::app-background-image', [
                    'appIdentifier' => 'packtrack',
                ])
            </div>
        </flux:tab.panel>

        <flux:tab.panel name="einstellungen">
            <div style="min-height: 400px;">
                @livewire('intranet-app-base::admin-settings', [
                    'appIdentifier' => 'packtrack',
                    'settingsModelClass' => '\Hwkdo\IntranetAppPacktrack\Models\IntranetAppPacktrackSettings',
                    'appSettingsClass' => '\Hwkdo\IntranetAppPacktrack\Data\AppSettings'
                ])
            </div>
        </flux:tab.panel>

        <flux:tab.panel name="statistiken">
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <flux:card class="flex flex-col gap-1">
                        <flux:text size="sm" class="text-zinc-500">Gesamt (inkl. gelöscht)</flux:text>
                        <div class="text-3xl font-bold">{{ $this->statistiken['gesamt'] }}</div>
                    </flux:card>
                    <flux:card class="flex flex-col gap-1">
                        <flux:text size="sm" class="text-zinc-500">Offen</flux:text>
                        <div class="text-3xl font-bold text-amber-500">{{ $this->statistiken['offen'] }}</div>
                    </flux:card>
                    <flux:card class="flex flex-col gap-1">
                        <flux:text size="sm" class="text-zinc-500">Abgeholt</flux:text>
                        <div class="text-3xl font-bold text-green-600">{{ $this->statistiken['abgeholt'] }}</div>
                    </flux:card>
                    <flux:card class="flex flex-col gap-1">
                        <flux:text size="sm" class="text-zinc-500">Gelöscht</flux:text>
                        <div class="text-3xl font-bold text-red-500">{{ $this->statistiken['geloescht'] }}</div>
                    </flux:card>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:card class="flex flex-col gap-1">
                        <flux:text size="sm" class="text-zinc-500">Heute angenommen</flux:text>
                        <div class="text-2xl font-bold">{{ $this->statistiken['heuteAngenommen'] }}</div>
                    </flux:card>
                    <flux:card class="flex flex-col gap-1">
                        <flux:text size="sm" class="text-zinc-500">Heute abgeholt</flux:text>
                        <div class="text-2xl font-bold">{{ $this->statistiken['heuteAbgeholt'] }}</div>
                    </flux:card>
                    <flux:card class="flex flex-col gap-1">
                        <flux:text size="sm" class="text-zinc-500">Diese Woche angenommen</flux:text>
                        <div class="text-2xl font-bold">{{ $this->statistiken['dieseWocheAngenommen'] }}</div>
                    </flux:card>
                </div>

                @if($this->statistiken['altestesOffen'])
                    <flux:callout icon="exclamation-triangle" color="yellow">
                        <flux:callout.heading>Ältestes offenes Paket</flux:callout.heading>
                        <flux:callout.text>
                            Seit {{ \Carbon\Carbon::parse($this->statistiken['altestesOffen'])->format('d.m.Y') }}
                            ({{ \Carbon\Carbon::parse($this->statistiken['altestesOffen'])->diffForHumans() }}) wartet ein Paket auf Abholung.
                        </flux:callout.text>
                    </flux:callout>
                @endif
            </div>
        </flux:tab.panel>

        <flux:tab.panel name="packetdienste">
            <div class="space-y-4">
                {{-- Neuen Dienst anlegen --}}
                <flux:card>
                    <flux:heading size="md" class="mb-3">Neuen Packetdienst hinzufügen</flux:heading>
                    <div class="flex gap-2">
                        <flux:input
                            wire:model="neuerDienstName"
                            @keydown.enter.prevent="$wire.dienstSpeichern()"
                            placeholder="Name des Packetdienstes ..."
                            class="flex-1"
                        />
                        <flux:button wire:click="dienstSpeichern" variant="primary" icon="plus">
                            Hinzufügen
                        </flux:button>
                    </div>
                    <flux:error name="neuerDienstName" />
                </flux:card>

                {{-- Liste --}}
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Name</flux:table.column>
                        <flux:table.column>Pakete</flux:table.column>
                        <flux:table.column>Aktionen</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->packetdienste as $dienst)
                            <flux:table.row wire:key="dienst-{{ $dienst->id }}">
                                <flux:table.cell>
                                    @if($editDienstId === $dienst->id)
                                        <div class="flex gap-2 items-center">
                                            <flux:input
                                                wire:model="editDienstName"
                                                @keydown.enter.prevent="$wire.dienstBearbeitenSpeichern()"
                                                @keydown.escape.prevent="$wire.set('editDienstId', null)"
                                                size="sm"
                                                autofocus
                                            />
                                            <flux:button wire:click="dienstBearbeitenSpeichern" size="sm" variant="primary" icon="check" />
                                            <flux:button wire:click="$set('editDienstId', null)" size="sm" variant="ghost" icon="x-mark" />
                                        </div>
                                        <flux:error name="editDienstName" />
                                    @else
                                        {{ $dienst->name }}
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm">{{ $dienst->pakete_count }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-1">
                                        <flux:button
                                            wire:click="dienstBearbeitenOeffnen({{ $dienst->id }})"
                                            size="sm"
                                            variant="ghost"
                                            icon="pencil"
                                        />
                                        <flux:button
                                            wire:click="dienstLoeschen({{ $dienst->id }})"
                                            wire:confirm="Packetdienst '{{ $dienst->name }}' löschen?"
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            class="text-zinc-400 hover:text-red-500"
                                            :disabled="$dienst->pakete_count > 0"
                                        />
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:tab.panel>
    </flux:tab.group>
</x-intranet-app-packtrack::packtrack-layout>
