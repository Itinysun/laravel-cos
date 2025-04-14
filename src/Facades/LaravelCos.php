<?php

namespace Itinysun\LaravelCos\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Itinysun\LaravelCos\LaravelCos
 */
class LaravelCos extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Itinysun\LaravelCos\LaravelCos::class;
    }
}
