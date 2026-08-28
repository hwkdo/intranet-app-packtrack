<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppPacktrack;

use Hwkdo\IntranetAppBase\Data\NotificationTypeDefinition;
use Hwkdo\IntranetAppBase\Interfaces\IntranetAppInterface;
use Hwkdo\IntranetAppBase\Interfaces\ProvidesNotificationsInterface;
use Illuminate\Support\Collection;

class IntranetAppPacktrack implements IntranetAppInterface, ProvidesNotificationsInterface
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
        return null;
    }

    public static function appSettingsClass(): ?string
    {
        return \Hwkdo\IntranetAppPacktrack\Data\AppSettings::class;
    }

    public static function mcpServers(): array
    {
        return [];
    }

    public static function notificationTypes(): array
    {
        return [
            new NotificationTypeDefinition(
                key: 'packtrack.paket_empfang',
                label: 'Paket eingegangen',
                appIdentifier: self::identifier(),
                appName: self::app_name(),
                mandatory: true,
            ),
            new NotificationTypeDefinition(
                key: 'packtrack.paket_ausgabe',
                label: 'Paket ausgegeben',
                appIdentifier: self::identifier(),
                appName: self::app_name(),
                mandatory: true,
            ),
            new NotificationTypeDefinition(
                key: 'packtrack.paket_reminder',
                label: 'Paket-Erinnerung',
                appIdentifier: self::identifier(),
                appName: self::app_name(),
                mandatory: false,
                defaultEnabled: true,
            ),
        ];
    }
}
