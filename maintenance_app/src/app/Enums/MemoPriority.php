<?php

namespace App\Enums;

enum MemoPriority: string
{
    case Normal = 'normal';
    case Important = 'important';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Normal => '通常',
            self::Important => '重要',
            self::Urgent => '緊急',
        };
    }
}
