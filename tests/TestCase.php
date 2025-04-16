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
    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        $currentDir = __DIR__ . '/';
        $temp_config = $currentDir . 'cos_config.php';
        if (!file_exists($temp_config)) {
            file_put_contents($temp_config, '<?php return [];');
        }
        $config = require $temp_config;
        if (empty($config)) {
            echo "\033[31mplease make sure tests/cos_config.php is not empty , otherwise all the tests will skip\033[0m\n";
            config()->set('cos.default', []);
        } else {
            config()->set('cos.default', $config);
            config()->set('filesystems.disks.cos', $config);
        }

        $data = require $currentDir . 'data.php';
        config()->set('data', $data);

        Storage::extend('cos', function ($app, $config) {
            $adapter = new CosFilesystemAdapter($config);

            return new FilesystemAdapter(new Filesystem($adapter), $adapter);
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelCosServiceProvider::class,
        ];
    }
}
