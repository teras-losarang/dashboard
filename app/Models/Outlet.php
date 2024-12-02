<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Outlet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'longitude',
        'latitude',
        'address',
        'operational_hour',
        'created_by',
        'images',
    ];

    protected $casts = [
        'operational_hour' => 'array',
        'images' => 'json',
    ];

    protected static function booted()
    {
        static::deleted(function (Outlet $outlet) {
            foreach (json_decode($outlet->images, true) as $image) {
                Storage::delete($image);
            }
        });

        static::updated(function (Outlet $outlet) {
            $imageToDelete = array_diff(json_decode($outlet->getOriginal('images'), true), json_decode(json_decode($outlet->attributes["images"], true), true));

            foreach ($imageToDelete as $image) {
                Storage::delete($image);
            }
        });
    }

    public function setNameAttribute($value)
    {
        $this->attributes["name"] = $value;
        $this->attributes["slug"] = Str::slug($value);
        $this->attributes["created_by"] = request()->user()->name;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
