<?php

namespace Itinysun\LaravelCos\Enums;

use Illuminate\Validation\Rules\Enum;
use League\Flysystem\Visibility;

enum ObjectAcl:string
{
    case PRIVATE = 'private'; // 私有读写
    case PUBLIC_READ = 'public-read'; // 公有读私有写
    case DEFAULT = 'default'; // 默认读写
    case AUTHENTICATED_READ = 'authenticated-read'; // 认证读写
    case BUCKET_OWNER_READ = 'bucket-owner-read'; // 桶拥有者读
    case BUCKET_OWNER_FULL_CONTROL = 'bucket-owner-full-control'; // 桶拥有者完全控制

    public function label(): string{
        return match ($this) {
            self::PRIVATE => '私有读写',
            self::PUBLIC_READ => '公有读私有写',
            self::DEFAULT => '默认读写',
            self::AUTHENTICATED_READ => '认证读写',
            self::BUCKET_OWNER_READ => '桶拥有者读',
            self::BUCKET_OWNER_FULL_CONTROL => '桶拥有者完全控制',
        };
    }

    public static function fromString (string $value): self
    {
        return match ($value) {
            'private' => self::PRIVATE,
            'public-read' => self::PUBLIC_READ,
            'authenticated-read' => self::AUTHENTICATED_READ,
            'bucket-owner-read' => self::BUCKET_OWNER_READ,
            'bucket-owner-full-control' => self::BUCKET_OWNER_FULL_CONTROL,
            default => self::DEFAULT,
        };
    }
    public static function fromVisibility(string $visibility): self
    {
        return match ($visibility) {
            Visibility::PUBLIC => self::PUBLIC_READ,
            Visibility::PRIVATE => self::PRIVATE,
            default => self::DEFAULT,
        };
    }
    public static function fromPermission(string $permission): self
    {
        return match ($permission) {
            'READ', 'FULL_CONTROL' => self::PUBLIC_READ,
            default => self::PRIVATE,
        };
    }
}
