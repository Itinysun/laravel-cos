<?php

/*
 * cos_config.php is a configuration file for the Laravel COS package.
 * It is only used for local testing and should not be uploaded to the repository.
 */
$currentDir = __DIR__ . '/';
if (!file_exists($currentDir . 'cos_config.php')) {
    file_put_contents($currentDir . 'cos_config.php', '<?php return [];');
}
$config = [];
/**
 * @throws Exception
 */
function getConfig(): array
{
    global $config;
    if (empty($config)) {
        $config = include __DIR__ . '/cos_config.php';
    }
    if (!is_array($config)) {
        throw new \Exception('cos_config.php must return an array');
    }
    return $config;
}

$client = null;
function getCos()
{
    global $client;
    if ($client) {
        return $client;
    }
    \Illuminate\Support\Facades\Config::set('cos.default', getConfig());
    return new \Itinysun\LaravelCos\LaravelCos();
}

it('test acl success', function () {
    $laravelCos = getCos();
    $acl = $laravelCos->getFileAcl('2025/01/01JH85ZBKZ9VCW0BF2V529P770.jpg');
    $this->assertEquals($acl, \Itinysun\LaravelCos\Enums\ObjectAcl::PUBLIC_READ);
});

it('test attr success', function () {
    $laravelCos = getCos();
    $attr = $laravelCos->getFileAttr('2025/01/01JH85ZBKZ9VCW0BF2V529P770.jpg');
    $this->assertEquals($attr->storageClass, \Itinysun\LaravelCos\Enums\StorageClass::STANDARD);
});
