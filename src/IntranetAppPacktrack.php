<?php

namespace Hwkdo\IntranetAppPacktrack;

use Hwkdo\IntranetAppBase\Interfaces\IntranetAppInterface;
use Illuminate\Support\Collection;

class IntranetAppPacktrack implements IntranetAppInterface
{
    public static function app_name(): string
    {
        return 'Packtrack';
    }

    public static function app_icon(): string
    {
        return 'magnifying-glass';
    }

    public static function identifier(): string
    {
        return 'packtrack';
    }

    public static function roles_admin(): Collection
    {
        return collect(config('intranet-app-packtrack.roles.admin'));
    }

    public static function roles_user(): Collection
    {
        return collect(config('intranet-app-packtrack.roles.user'));
    }

    public static function userSettingsClass(): ?string
    {
        return \Hwkdo\IntranetAppPacktrack\Data\UserSettings::class;
    }

    public static function appSettingsClass(): ?string
    {
        return \Hwkdo\IntranetAppPacktrack\Data\AppSettings::class;
    }

    public static function mcpServers(): array
    {
        return [];
    }
}
