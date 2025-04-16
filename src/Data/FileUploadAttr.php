<?php

namespace Itinysun\LaravelCos\Data;
use Spatie\LaravelData\Attributes\MapName;

class FileUploadAttr extends FileAttr
{
    #[mapName('Progress')]
    public mixed $progressCallback;
    #[mapName('PartSize')]
    public int $partSize;
    #[mapName('Concurrency')]
    public int $concurrency;
    #[mapName('ContentMD5')]
    public bool $contentMD5;
    #[mapName('ServerSideEncryption')]
    public string $serverSideEncryption;
    #[mapName('TrafficLimit')]
    public int $trafficLimit;
}
