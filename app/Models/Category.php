<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'type',
        'status',
    ];

    protected static function booted()
    {
        static::deleted(function (Category $category) {
            Storage::delete($category->image);
        });

        static::updated(function (Category $category) {
            $imageToDelete = array_diff([$category->getOriginal('image')], [$category->attributes["image"]]);

            foreach ($imageToDelete as $image) {
                Storage::delete($image);
            }
        });
    }

    public function setNameAttribute($value)
    {
        $this->attributes["name"] = $value;
        $this->attributes["slug"] = Str::slug($value);
    }
}
