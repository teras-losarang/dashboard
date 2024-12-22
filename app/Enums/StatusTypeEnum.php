<?php

namespace App\Enums;

class StatusTypeEnum
{
    const ORDER = 1;
    const CANCEL = 88;
    const DONE = 99;

    public static function all(): array
    {
        return [
            self::ORDER => "Order",
            self::CANCEL => "Cancel",
            self::DONE => "Done",
        ];
    }

    public static function show($id): string
    {
        return self::all()[$id];
    }
}
