<?php

namespace Itinysun\LaravelCos;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;

class CosStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Storage::extend('cos', function ($app, $config) {
            $adapter = new CosFilesystemAdapter($config);
            return new FilesystemAdapter(new Filesystem($adapter), $adapter);
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }
}
