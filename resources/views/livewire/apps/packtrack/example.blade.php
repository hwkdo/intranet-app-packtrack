<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('PackTrack – Beispiel')] class extends Component {
} ?>

<x-intranet-app-packtrack::packtrack-layout heading="PackTrack" subheading="Beispielseite">
    <flux:card>
        <flux:heading size="lg">Beispielseite</flux:heading>
        <flux:text>Diese Seite dient als Beispiel.</flux:text>
    </flux:card>
</x-intranet-app-packtrack::packtrack-layout>
