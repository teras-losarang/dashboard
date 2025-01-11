<?php

namespace App\Enums;

class CategoryDirectionEnum
{
    const HORIZONTAL = 1;
    const VERTICAL = 2;

    public static function all(): array
    {
        return [
            self::HORIZONTAL => "Horizontal",
            self::VERTICAL => "Vertical",
        ];
    }

    public static function show($id): string
    {
        return self::all()[$id];
    }
}
