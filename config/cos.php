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
        'signed_url' => env('COS_SIGNED_URL', false),

        // 可选，是否使用 https，默认 true
        'use_https' => env('COS_USE_HTTPS', true),

        // 可选，自定义域名
        'domain' => env('COS_DOMAIN', ''),

        // 可选，使用 CDN 域名时指定生成的 URL host
        'cdn' => env('COS_CDN', ''),

        'prefix' => env('COS_PREFIX', ''), // 全局路径前缀

        // 性能配置
        'timeout' => env('COS_TIMEOUT', 60),
        'chunk_size' => env('COS_CHUNK_SIZE', 20 * 1024 * 1024), // 分块大小 20MB
        'concurrency' => env('COS_CONCURRENCY', 5), // 并发数
        'max_retries' => env('COS_MAX_RETRIES', 3), // 最大重试次数
    ],
];
