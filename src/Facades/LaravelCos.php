<?php

namespace Itinysun\LaravelCos\Facades;

use Exception;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Itinysun\LaravelCos\Lib\LaravelCos
 *
 * @mixin \Itinysun\LaravelCos\Lib\LaravelCos
 */
class LaravelCos extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Itinysun\LaravelCos\Lib\LaravelCos::class;
    }

    /**
     * @throws Exception
     */
    public static function registerInstance($configName): void
    {
        $instance = new \Itinysun\LaravelCos\Lib\LaravelCos($configName);
        app()->instance(\Itinysun\LaravelCos\Lib\LaravelCos::class, $instance);
    }
}
