<?php

namespace Itinysun\LaravelCos\Tests;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Itinysun\LaravelCos\CosFilesystemAdapter;
use Itinysun\LaravelCos\LaravelCosServiceProvider;
use League\Flysystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    private $configFile = 'test_config.php';
    private $logFile = 'test.log';

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        //load test config
        $config = require $this->configFile;
        if (empty($config)) {
            echo "\033[31mplease make sure tests/cos_config.php is not empty , otherwise all the tests will skip\033[0m\n";
            config()->set('cos.default', []);
        } else {
            config()->set('cos.default', $config);
            config()->set('filesystems.disks.cos', $config);
        }

        //use spatie/laravel-data
        $data = require './vendor/spatie/laravel-data/config/data.php';
        config()->set('data', $data);

        //enable the filesystem
        Storage::extend('cos', function ($app, $config) {
            $adapter = new CosFilesystemAdapter($config);
            return new FilesystemAdapter(new Filesystem($adapter), $adapter);
        });

        //enable logging
        config()->set('logging.default', 'single');
        config()->set('logging.channels.single', [
            'driver' => 'single',
            'path' => 'test.log',
            'level' => 'debug',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (!file_exists($this->configFile)) {
            file_put_contents($this->configFile, '<?php return [];');
        }
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelCosServiceProvider::class,
        ];
    }
}
