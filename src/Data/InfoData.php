<?php

namespace Itinysun\LaravelCos\Data;

use Carbon\Carbon;
use Itinysun\LaravelCos\Enums\StorageClass;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class InfoData extends Data
{
    public function __construct(
        #[MapInputName('Key')]
        public string $key,
        #[MapInputName('LastModified')]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.v\Z')]
        public Carbon $lastModified,
        #[MapInputName('Size')]
        public int $size,
        #[MapInputName('ETag')]
        public string $eTag,
        #[MapInputName('StorageClass')]
        #[WithCast(EnumCast::class)]
        public StorageClass $storageClass,
    ) {}
}
