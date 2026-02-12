<?php

namespace Itinysun\LaravelCos\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class FileDownloadAttr extends Data
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
