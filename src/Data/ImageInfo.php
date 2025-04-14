<?php

namespace Itinysun\LaravelCos\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;

class ImageInfo extends Data
{
    public function __construct(
        public string $format = '',
        public int $width = 0,
        public int $height = 0,
        public int $size = 0,
        public string $md5 = '',
        #[MapName('photo_rgb')]
        public string $photoRgb = '',
        #[MapName('frame_count')]
        public int $frameCount = 0,
        #[MapName('bit_depth')]
        public int $bitDepth = 0,
        #[MapName('vertical_dpi')]
        public int $verticalDpi = 0,
        #[MapName('horizontal_dpi')]
        public int $horizontalDpi = 0,
    ) {
    }
}
