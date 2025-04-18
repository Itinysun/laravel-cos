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

class FileCopyAttr extends Data
{
    #[mapName('ACL')]
    #[WithCast(EnumCast::class)]
    #[WithTransformer(EnumTransformer::class)]
    public ?ObjectAcl $acl;
    #[mapName('CacheControl')]
    public ?string $cacheControl;
    #[mapName('ContentDisposition')]
    public ?string $contentDisposition;
    #[mapName('ContentEncoding')]
    public ?string $contentEncoding;
    #[mapName('ContentLanguage')]
    public ?string $contentLanguage;
    #[mapName('Expires')]
    public ?string $expires;
    #[mapName('Metadata')]
    public ?array $metadata;
    #[mapName('StorageClass')]
    #[WithCast(EnumCast::class)]
    #[WithTransformer(EnumTransformer::class)]
    public ?StorageClass $storageClass;
    #[mapName('ServerSideEncryption')]
    public ?string $serverSideEncryption;
}
