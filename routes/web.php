<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'can:see-app-packtrack'])->group(function () {
    Route::livewire('apps/packtrack', 'intranet-app-packtrack::apps.packtrack.index')
        ->name('apps.packtrack.index');

    Route::livewire('apps/packtrack/annahme', 'intranet-app-packtrack::apps.packtrack.annahme')
        ->name('apps.packtrack.annahme');

    Route::livewire('apps/packtrack/ausgabe', 'intranet-app-packtrack::apps.packtrack.ausgabe')
        ->name('apps.packtrack.ausgabe');

    Route::livewire('apps/packtrack/pakete', 'intranet-app-packtrack::apps.packtrack.pakete.index')
        ->name('apps.packtrack.pakete.index');

    Route::livewire('apps/packtrack/settings/user', 'intranet-app-packtrack::apps.packtrack.settings.user')
        ->name('apps.packtrack.settings.user');
});

Route::middleware(['web', 'auth', 'can:manage-app-packtrack'])->group(function () {
    Route::livewire('apps/packtrack/admin', 'intranet-app-packtrack::apps.packtrack.admin.index')
        ->name('apps.packtrack.admin.index');
});
