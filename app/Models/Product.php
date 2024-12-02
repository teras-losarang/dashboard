<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "outlet_id",
        "name",
        "slug",
        "price",
        "enable_variant",
        "status",
        "images",
        "created_by",
    ];

    protected $casts = [
        'images' => 'json',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes["name"] = $value;
        $this->attributes["slug"] = Str::slug($value);
        $this->attributes["created_by"] = request()->user()->name;
    }
}
