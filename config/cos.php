<?php

// config for Itinysun/LaravelCos
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

        'guzzle' => [
            'timeout' => '60',
            'connect_timeout' => '60',
        ],
    ],
];
