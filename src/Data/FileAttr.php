<?php

namespace Itinysun\LaravelCos\Data;

use Itinysun\LaravelCos\Enums\ObjectAcl;
use Itinysun\LaravelCos\Enums\StorageClass;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class FileAttr extends Data
{

    #[mapName('StorageClass')]
    public StorageClass $storageClass;
    #[mapName('LastModified')]
    public string $lastModified;
    #[mapName('CacheControl')]
    public ?string $cacheControl;
    #[mapName('ContentType')]
    public string $contentType;
    #[mapName('ContentLength')]
    public int $contentLength;
    #[mapName('ContentEncoding')]
    public ?string $contentEncoding;
    #[mapName('ContentDisposition')]
    public ?string $contentDisposition;
    #[mapName('ContentLanguage')]
    public string $contentLanguage;
    #[mapName('Expires')]
    public ?string $expires;
    #[mapName('Metadata')]
    public array $metadata;
    #[mapName('ServerSideEncryption')]
    public string $serverSideEncryption;
    #[mapName('ETag')]
    public string $eTag;
    #[mapName('CRC')]
    public ?string $crc;

    //for archive file header
    #[mapName('Restore')]
    public ?string $restore;
    #[mapName('RestoreStatus')]
    public ?string $restoreStatus;

    //for version control
    #[mapName('VersionId')]
    public ?string $versionId;

    //for storage tier
    #[mapName('StorageTier')]
    public ?StorageClass $storageTier;
}
