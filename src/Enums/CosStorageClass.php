<?php

namespace Itinysun\LaravelCos\Enums;

enum CosStorageClass: string
{
    case STANDARD = 'STANDARD'; // 标准存储
    case STANDARD_IA = 'STANDARD_IA'; // 低频存储
    case ARCHIVE = 'ARCHIVE'; // 归档存储
    case DEEP_ARCHIVE = 'DEEP_ARCHIVE'; // 深度归档存储
    case INTELLIGENT_TIERING = 'INTELLIGENT_TIERING'; // 智能分层存储
    case MAZ_STANDARD = 'MAZ_STANDARD'; // 标准存储（多 AZ）
    case MAZ_STANDARD_IA = 'MAZ_STANDARD_IA'; // 低频存储（多 AZ）
    case MAZ_INTELLIGENT_TIERING = 'MAZ_INTELLIGENT_TIERING'; // 智能分层存储（多 AZ）

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => '标准存储',
            self::STANDARD_IA => '低频存储',
            self::ARCHIVE => '归档存储',
            self::DEEP_ARCHIVE => '深度归档存储',
            self::INTELLIGENT_TIERING => '智能分层存储',
            self::MAZ_STANDARD => '标准存储（多 AZ）',
            self::MAZ_STANDARD_IA => '低频存储（多 AZ）',
            self::MAZ_INTELLIGENT_TIERING => '智能分层存储（多 AZ）',
        };
    }
}
