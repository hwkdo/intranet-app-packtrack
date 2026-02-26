<?php

namespace Hwkdo\IntranetAppPacktrack\Models;

use Hwkdo\IntranetAppPacktrack\Data\AppSettings;
use Illuminate\Database\Eloquent\Model;

class IntranetAppPacktrackSettings extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'settings' => AppSettings::class.':default',
        ];
    }

    public static function current(): ?IntranetAppPacktrackSettings
    {
        return self::orderBy('version', 'desc')->first();
    }
}
