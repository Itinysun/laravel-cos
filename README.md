# Flysystem sdk for Tencent Cos

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itinysun/laravel-cos.svg?style=flat-square)](https://packagist.org/packages/itinysun/laravel-cos)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/itinysun/laravel-cos/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/itinysun/laravel-cos/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/itinysun/laravel-cos/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/itinysun/laravel-cos/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/itinysun/laravel-cos.svg?style=flat-square)](https://packagist.org/packages/itinysun/laravel-cos)


## 一个使用腾讯云原生SDK实现的laravel文件存储扩展包,支持切片上传和下载


## Installation 安装

#### 使用composer 安装 You can install the package via composer:

```bash
composer require itinysun/laravel-cos
```

#### 发布配置文件 You can publish the config file with:
```bash
php artisan vendor:publish --tag="cos-config"
```

This is the contents of the published config file:

```php
return [
    'default' => [
        'app_id' => env('COS_APP_ID'),
        'secret_id' => env('COS_SECRET_ID'),
        'secret_key' => env('COS_SECRET_KEY'),
        'bucket' => env('COS_BUCKET'),  // 不带数字 app_id 后缀

        'region' => 'ap-beijing',

        // 可选，如果 bucket 为私有访问请打开此项
        'signed_url' => true,

        // 可选，是否使用 https，默认 false
        'use_https' => true,

        // 可选，自定义域名
        'domain' => '',

        // 可选，使用 CDN 域名时指定生成的 URL host
        'cdn' => '',

        'prefix' => '', // 全局路径前缀
    ],
];
```

## Usage

```php
//直接使用cos客户端
$laravelCos = new Itinysun\LaravelCos\Lib\LaravelCos('default');

// 上传文件
$laravelCos->uploadFile('test.txt', 'local/test.txt');

//使用facade
use Itinysun\LaravelCos\Facades\LaravelCos;
LaravelCos::uploadFile('test.txt', 'local/test.txt');

//使用laravel的storage
//首先在config/filesystems.php 中添加一个 disk,config_name 是上面配置文件中的key
        'cos_disk' => [
            'driver' => 'cos',
            'config_name' => 'cos_source',
        ],

//然后就可以使用laravel的storage了

```

## Testing
扩展包使用[TestBench](https://packages.tools/getting-started.html)进行测试,不需要安装laravel

```bash
### 发布测试文件
composer build
```
### 填写一个cos配置到 test_config.php , 切记这个文件不要提交到版本控制,已经在.gitignore中忽略了
```php

```bash
### 运行测试
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Credits

- [Itinysun](https://github.com/Itinysun)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
