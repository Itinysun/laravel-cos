<?php

namespace Itinysun\LaravelCos\Tests;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Itinysun\LaravelCos\LaravelCosServiceProvider;
use Itinysun\LaravelCos\Lib\CosFilesystemAdapter;
use League\Flysystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;
use Orchestra\Testbench\Concerns\WithWorkbench;

class TestCase extends Orchestra
{
    use WithWorkbench;
    private string $configFile = 'test_config.php';
    private string $logFile = 'test.log';

    public function getEnvironmentSetUp($app): void
    {
        if (!file_exists($this->configFile)) {
            file_put_contents($this->configFile, '<?php return [];');
        }
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }

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

    protected function getApplicationTimezone($app)
    {
        return 'Asia/Shanghai';
    }
}
