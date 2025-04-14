<?php

namespace Itinysun\LaravelCos;

use Itinysun\LaravelCos\Commands\LaravelCosCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCosServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-cos')
            ->hasConfigFile('cos')
            ->hasCommand(LaravelCosCommand::class);
    }
}
