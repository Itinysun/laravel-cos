<?php

namespace Itinysun\LaravelCos\Data;
use Spatie\LaravelData\Attributes\MapName;

class FileDownloadAttr extends FileAttr
{
        #[mapName('Progress')]
    public mixed $progressCallback;
    #[mapName('PartSize')]
    public int $partSize;
    #[mapName('Concurrency')]
    public int $concurrency;
    #[mapName('ResumableDownload')]
    public bool $resumableDownload;
    #[mapName('ResumableTaskFile')]
    public string $resumableTaskFile;
}
