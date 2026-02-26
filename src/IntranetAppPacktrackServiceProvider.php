<?php

namespace Hwkdo\IntranetAppPacktrack;

use Hwkdo\IntranetAppPacktrack\Commands\PacktrackReminderCommand;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class IntranetAppPacktrackServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('intranet-app-packtrack')
            ->hasConfigFile()
            ->hasViews()
            ->discoversMigrations()
            ->hasCommand(PacktrackReminderCommand::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->app->booted(function () {
            Livewire::addNamespace('intranet-app-packtrack', __DIR__.'/../resources/views/livewire');
        });

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
