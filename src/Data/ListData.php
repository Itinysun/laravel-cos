<?php

namespace Itinysun\LaravelCos\Data;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class ListData extends Data
{
    public function __construct(
        #[MapInputName('IsTruncated')]
        public bool $isTruncated,
        #[MapInputName('NextMarker')]
        public ?string $nextMarker,
        #[MapInputName('MaxKeys')]
        public int $maxKeys,
        #[MapInputName('Prefix')]
        public string $prefix,
        #[MapInputName('Marker')]
        public string $marker,
        #[MapInputName('RequestId')]
        public string $requestId,
        #[MapInputName('Contents')]
        #[DataCollectionOf(InfoData::class)]
        public ?Collection $contents,
        #[MapInputName('CommonPrefixes')]
        public ?array $commonPrefixes,
    ) {}

    /**
     * @return Collection<InfoData>
     */
    public function getFiles(): Collection
    {
        if ($this->contents && ! $this->contents->isEmpty()) {
            return $this->contents->filter(function ($item) {
                return ! Str::endsWith($item->key, '/');
            });
        }

        return collect();
    }

    /**
     * @return Collection<string>
     */
    public function getDirs(): Collection
    {
        if ($this->commonPrefixes) {
            Log::info('getDirs', ['commonPrefixes' => $this->commonPrefixes]);

            return collect($this->commonPrefixes)->map(function ($item) {
                return $item['Prefix'];
            });
        }

        if (! $this->contents) {
            return collect();
        }

        return collect($this->contents)->map(function ($item) {
            return $item->key;
        })->filter(function ($item) {
            return Str::endsWith($item, '/');
        });
    }
}
