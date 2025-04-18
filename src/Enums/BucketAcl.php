<?php

namespace Itinysun\LaravelCos\Enums;

use Itinysun\LaravelCos\Exceptions\CosFilesystemException;

enum BucketAcl: string
{
    case PRIVATE = 'private'; // 私有读写
    case PUBLIC_READ = 'public-read'; // 公有读私有写
    case PUBLIC_READ_WRITE = 'public-read-write'; // 公有读写
    case AUTHENTICATED_READ = 'authenticated-read'; // 认证读写

    public function label(): string
    {
        return match ($this) {
            self::PRIVATE => '私有读写',
            self::PUBLIC_READ => '公有读私有写',
            self::PUBLIC_READ_WRITE => '公有读写',
            self::AUTHENTICATED_READ => '认证读写',
        };
    }

    /**
     * @throws CosFilesystemException
     */
    public static function fromString(string $value): self
    {
        return match ($value) {
            'private' => self::PRIVATE,
            'public-read' => self::PUBLIC_READ,
            'public-read-write' => self::PUBLIC_READ_WRITE,
            'authenticated-read' => self::AUTHENTICATED_READ,
            default => throw new CosFilesystemException('Invalid BucketAcl value for'.$value),
        };
    }
}
