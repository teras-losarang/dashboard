<?php

namespace App\Facades;

use Illuminate\Database\Eloquent\Model;

class Helper
{
    public static function onlyFillables($attributes = [], Model $model): array
    {
        $attributeFillables = [];
        $fillables = $model->getFillable();

        foreach ($fillables as $index => $fillable) {
            if (array_key_exists($fillable, $attributes)) {
                $attributeFillables[$fillable] = $attributes[$fillable];
            }
        }

        return $attributeFillables;
    }
}
