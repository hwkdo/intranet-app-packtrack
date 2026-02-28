@props([
    'heading' => '',
    'subheading' => '',
    'navItems' => []
])

@php
    $defaultNavItems = [
        ['label' => 'Übersicht', 'href' => route('apps.packtrack.index'), 'icon' => 'home', 'description' => 'App-Übersicht', 'buttonText' => 'Übersicht anzeigen'],
        ['label' => 'Paketannahme', 'href' => route('apps.packtrack.annahme'), 'icon' => 'inbox-arrow-down', 'description' => 'Neue Pakete annehmen', 'buttonText' => 'Pakete annehmen'],
        ['label' => 'Paketausgabe', 'href' => route('apps.packtrack.ausgabe'), 'icon' => 'arrow-up-tray', 'description' => 'Pakete ausgeben', 'buttonText' => 'Pakete ausgeben'],
        ['label' => 'Alle Pakete', 'href' => route('apps.packtrack.pakete.index'), 'icon' => 'archive-box', 'description' => 'Paketübersicht', 'buttonText' => 'Pakete anzeigen'],
        ['label' => 'Meine Einstellungen', 'href' => route('apps.packtrack.settings.user'), 'icon' => 'cog-6-tooth', 'description' => 'Persönliche Einstellungen', 'buttonText' => 'Einstellungen öffnen'],
        ['label' => 'Admin', 'href' => route('apps.packtrack.admin.index'), 'icon' => 'shield-check', 'description' => 'Administrationsbereich', 'buttonText' => 'Admin öffnen', 'permission' => 'manage-app-packtrack'],
    ];

    $navItems = !empty($navItems) ? $navItems : $defaultNavItems;
    $customBgUrl = \Hwkdo\IntranetAppBase\Models\AppBackground::getCustomBackgroundUrl('packtrack');
@endphp

@if($customBgUrl)
    @push('app-styles')
    <style data-app-bg data-ts="{{ uniqid() }}">
        :root { --app-bg-image: url('{{ $customBgUrl }}'); }
    </style>
    @endpush
@endif

@if(request()->routeIs('apps.packtrack.index'))
    <x-intranet-app-base::app-layout
        app-identifier="packtrack"
        :heading="$heading"
        :subheading="$subheading"
        :nav-items="$navItems"
        :wrap-in-card="false"
    >
        <x-intranet-app-base::app-index-auto
            app-identifier="packtrack"
            app-name="Packtrack"
            app-description="Paketverfolgung für die Poststelle"
            :nav-items="$navItems"
            welcome-title="Willkommen bei PackTrack"
            welcome-description="Verwalten Sie Pakete an der Poststelle effizient mit Barcode-Scanner-Unterstützung."
        />
    </x-intranet-app-base::app-layout>
@else
    <x-intranet-app-base::app-layout
        app-identifier="packtrack"
        :heading="$heading"
        :subheading="$subheading"
        :nav-items="$navItems"
        :wrap-in-card="true"
    >
        {{ $slot }}
    </x-intranet-app-base::app-layout>
@endif
