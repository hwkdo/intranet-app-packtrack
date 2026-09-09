<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('PackTrack – Benachrichtigungen')] class extends Component {
} ?>

<div>
    <x-intranet-app-packtrack::packtrack-layout heading="Benachrichtigungen" subheading="Benachrichtigungseinstellungen für PackTrack">
        @livewire('intranet-app-base::notification-settings', ['appIdentifier' => 'packtrack'])
    </x-intranet-app-packtrack::packtrack-layout>
</div>
