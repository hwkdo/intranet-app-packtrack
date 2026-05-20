<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('PackTrack – App-Info')] class extends Component {
} ?>

<x-intranet-app-packtrack::packtrack-layout heading="App-Info" subheading="Installierte Version und Release-Historie">
    @livewire('intranet-app-base::app-info', ['appIdentifier' => 'packtrack'])
</x-intranet-app-packtrack::packtrack-layout>
