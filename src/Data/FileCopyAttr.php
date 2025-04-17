<?php

namespace Itinysun\LaravelCos\Data;

use Itinysun\LaravelCos\Enums\ObjectAcl;
use Itinysun\LaravelCos\Enums\StorageClass;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class FileCopyAttr extends Data
{
   #[mapName('ACL')]
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
    public ?StorageClass $storageClass;
    #[mapName('ServerSideEncryption')]
    public ?string $serverSideEncryption;
}
