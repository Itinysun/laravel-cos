<?php

namespace Itinysun\LaravelCos\Data;

use Itinysun\LaravelCos\Enums\ObjectAcl;
use Itinysun\LaravelCos\Enums\StorageClass;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\EnumTransformer;

class FileUploadAttr extends Data
{
    #[mapName('Progress')]
    public mixed $progressCallback;
    #[mapName('PartSize')]
    public int $partSize;
    #[mapName('Concurrency')]
    public int $concurrency;
    #[mapName('ACL')]
    public ObjectAcl $acl;
    #[mapName('CacheControl')]
    public ?string $cacheControl;
    #[mapName('ContentDisposition')]
    public string $contentDisposition;
    #[mapName('ContentEncoding')]
    public string $contentEncoding;
    #[mapName('ContentLanguage')]
    public string $contentLanguage;
    #[mapName('ContentLength')]
    public int $contentLength;
    #[mapName('ContentType')]
    public string $contentType;
    #[mapName('Expires')]
    public string $expires;
    #[mapName('Metadata')]
    public array $metadata;
    #[mapName('StorageClass')]
    #[WithCast(EnumCast::class)]
    #[WithTransformer(EnumTransformer::class)]
    public StorageClass $storageClass;
    #[mapName('ContentMD5')]
    public bool $contentMD5;
    #[mapName('ServerSideEncryption')]
    public string $serverSideEncryption;
    #[mapName('TrafficLimit')]
    public int $trafficLimit;
}
