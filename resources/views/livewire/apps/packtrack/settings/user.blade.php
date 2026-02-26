<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('PackTrack – Meine Einstellungen')] class extends Component {
} ?>

<x-intranet-app-packtrack::packtrack-layout heading="PackTrack" subheading="Meine Einstellungen">
    @livewire('intranet-app-base::user-settings', ['appIdentifier' => 'packtrack'])
</x-intranet-app-packtrack::packtrack-layout>
