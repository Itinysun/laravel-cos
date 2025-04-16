<?php

namespace Itinysun\LaravelCos\Data;

use Carbon\Carbon;
use Itinysun\LaravelCos\Enums\StorageClass;
use League\Flysystem\FileAttributes;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class FileAttr extends Data
{
    #[mapName('Key')]
    public string $key;
    #[mapName('StorageClass')]
    #[WithCast(EnumCast::class)]
    public StorageClass $storageClass;
    #[MapInputName('LastModified')]
    #[WithCast(DateTimeInterfaceCast::class, format: 'D, d M Y H:i:s T')]
    public Carbon $lastModified;
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
    public ?array $metadata;
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
    public ?string $storageTier;

    public function toFileAttributes(): FileAttributes
    {
        return new FileAttributes($this->key, $this->contentLength ?? null, null, $this->lastModified->timestamp, $this->contentType ?? null);
    }
}
