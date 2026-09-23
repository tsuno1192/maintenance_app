<?php

namespace App\Enums;

enum MemoShift: string
{
    case Day = 'day';
    case Night = 'night';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Day => '日勤',
            self::Night => '夜勤',
            self::Other => 'その他',
        };
    }
}
