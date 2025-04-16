<?php

namespace Itinysun\LaravelCos\Data;

use Itinysun\LaravelCos\Enums\ObjectAcl;
use Itinysun\LaravelCos\Enums\StorageClass;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class FileAttr extends Data
{
    #[mapName('ACL')]
    public ObjectAcl $acl;
    #[mapName('StorageClass')]
    public StorageClass $storageClass;
    #[mapName('LastModified')]
    public string $lastModified;
    #[mapName('CacheControl')]
    public string $cacheControl;
    #[mapName('ContentType')]
    public string $contentType;
    #[mapName('ContentLength')]
    public int $contentLength;
    #[mapName('ContentEncoding')]
    public string $contentEncoding;
    #[mapName('ContentDisposition')]
    public string $contentDisposition;
    #[mapName('ContentLanguage')]
    public string $contentLanguage;
    #[mapName('Expires')]
    public string $expires;
    #[mapName('Metadata')]
    public array $metadata;
}
