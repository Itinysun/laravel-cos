<?php

namespace Itinysun\LaravelCos\Tests;

use Itinysun\LaravelCos\CosStorageServiceProvider;
use Itinysun\LaravelCos\LaravelCosServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{

    private string $configFile = 'test_config.php';

    public function getEnvironmentSetUp($app): void
    {
        $this->outLinkLogs();
        $this->loadCosTestConfig();
    }

    protected function outLinkLogs(): void
    {
        if (!is_dir('logs')) {
            $target = 'vendor/orchestra/testbench-core/laravel/storage/logs/';
            $link = 'logs';

            if (symlink($target, $link)) {
                echo "软链接创建成功：$link -> $target\n";
            } else {
                echo "软链接创建失败\n";
            }
        }
    }

    protected function loadCosTestConfig(): void
    {
        if (!file_exists($this->configFile)) {
            file_put_contents($this->configFile, '<?php return [];');
        }
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
            CosStorageServiceProvider::class
        ];
    }

    protected function getApplicationTimezone($app)
    {
        return 'Asia/Shanghai';
    }
}
