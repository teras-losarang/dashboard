<?php

namespace App\Enums;

class CategoryTypeEnum
{
    const DEFAULT = 1;
    const MENU = 2;
    const BANNER = 3;

    public static function all() : array {
        return [
            self::DEFAULT => "Default",
            self::MENU => "Menu",
            self::BANNER => "Banner",
        ];
    }

    public static function show($id) : string {
        return self::all()[$id];
    }
}
