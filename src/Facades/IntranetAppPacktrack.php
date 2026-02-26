<?php

namespace Hwkdo\IntranetAppPacktrack\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Hwkdo\IntranetAppPacktrack\IntranetAppPacktrack
 */
class IntranetAppPacktrack extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hwkdo\IntranetAppPacktrack\IntranetAppPacktrack::class;
    }
}
