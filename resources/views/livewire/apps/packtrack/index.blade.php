<?php

use Hwkdo\IntranetAppPacktrack\Models\Abholung;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('PackTrack – Übersicht')] class extends Component {
    public int $offenGesamt = 0;
    public int $offenEigen = 0;
    public int $heuteAngenommen = 0;
    public int $heuteAbgeholt = 0;

    public function mount(): void
    {
        $userId = auth()->id();

        $this->offenGesamt = Paket::nichtAbgeholt()->count();
        $this->offenEigen = Paket::nichtAbgeholtFuerUser($userId)->count();
        $this->heuteAngenommen = Paket::query()->whereDate('created_at', today())->count();
        $this->heuteAbgeholt = Abholung::query()->whereDate('created_at', today())->count();
    }
} ?>
<div>
<x-intranet-app-packtrack::packtrack-layout heading="PackTrack" subheading="Übersicht">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Offen (gesamt)</flux:text>
            <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $offenGesamt }}</div>
            <flux:text size="sm" class="text-zinc-500">nicht abgeholte Pakete</flux:text>
        </flux:card>
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Meine offenen Pakete</flux:text>
            <div class="text-3xl font-bold {{ $offenEigen > 0 ? 'text-amber-500' : 'text-zinc-900 dark:text-white' }}">{{ $offenEigen }}</div>
            <flux:text size="sm" class="text-zinc-500">für mich bereit</flux:text>
        </flux:card>
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Heute angenommen</flux:text>
            <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $heuteAngenommen }}</div>
            <flux:text size="sm" class="text-zinc-500">Pakete heute eingegangen</flux:text>
        </flux:card>
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Heute abgeholt</flux:text>
            <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $heuteAbgeholt }}</div>
            <flux:text size="sm" class="text-zinc-500">Pakete heute ausgegeben</flux:text>
        </flux:card>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <div class="flex items-center gap-3 mb-3">
                <div class="rounded-lg bg-blue-100 dark:bg-blue-900/30 p-2">
                    <flux:icon name="inbox-arrow-down" class="size-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:heading size="md">Paketannahme</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">Neue Pakete einscannen und erfassen</flux:text>
                </div>
            </div>
            <flux:button :href="route('apps.packtrack.annahme')" wire:navigate variant="primary" icon="inbox-arrow-down" class="w-full">
                Pakete annehmen
            </flux:button>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-3 mb-3">
                <div class="rounded-lg bg-green-100 dark:bg-green-900/30 p-2">
                    <flux:icon name="arrow-up-tray" class="size-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:heading size="md">Paketausgabe</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">Pakete scannen und ausgeben</flux:text>
                </div>
            </div>
            <flux:button :href="route('apps.packtrack.ausgabe')" wire:navigate variant="primary" icon="arrow-up-tray" class="w-full">
                Pakete ausgeben
            </flux:button>
        </flux:card>
    </div>
</x-intranet-app-packtrack::packtrack-layout>
</div>