<?php

namespace Itinysun\LaravelCos\Facades;

use Exception;
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

    /**
     * @throws Exception
     */
    public static function registerInstance($configName): void
    {
        $instance = new \Itinysun\LaravelCos\LaravelCos($configName);
        app()->instance(\Itinysun\LaravelCos\LaravelCos::class, $instance);
    }
}
