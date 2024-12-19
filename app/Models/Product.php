<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
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
        "description",
        "created_by",
    ];

    protected $casts = [
        'images' => 'json',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
        'outlet_id'
    ];

    protected static function booted()
    {
        static::deleted(function (Product $product) {
            foreach (json_decode($product->images, true) as $image) {
                Storage::delete($image);
            }
        });

        static::updated(function (Product $product) {
            $imageToDelete = array_diff(json_decode($product->getOriginal('images'), true), json_decode(json_decode($product->attributes["images"], true), true));

            foreach ($imageToDelete as $image) {
                Storage::delete($image);
            }
        });
    }

    public function setNameAttribute($value)
    {
        $this->attributes["name"] = $value;
        $this->attributes["slug"] = Str::slug($value . "-" . Str::random(6));
        $this->attributes["created_by"] = request()->user()->name;
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, "product_has_categories");
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
