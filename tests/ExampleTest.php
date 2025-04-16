<?php

$currentDir = __DIR__ . '/';
if(!file_exists($currentDir . 'cos_config.php')) {
    file_put_contents($currentDir . 'cos_config.php', '<?php return [];');
}
$config = [];
/**
 * @throws Exception
 */
function getConfig():array
{
    global $config;
    if(empty($config)) {
        $config = include __DIR__ . '/cos_config.php';
    }
    if (!is_array($config)) {
        throw new \Exception('cos_config.php must return an array');
    }
    return $config;
}

it('test acl succuss', function () {
    \Illuminate\Support\Facades\Config::set('cos.default',getConfig());
    $laravelCos = new \Itinysun\LaravelCos\LaravelCos();
    $acl = $laravelCos->getFileAcl('2025/01/01JH85ZBKZ9VCW0BF2V529P770.jpg');
    $this->assertEquals($acl, \Itinysun\LaravelCos\Enums\ObjectAcl::PUBLIC_READ);
});
