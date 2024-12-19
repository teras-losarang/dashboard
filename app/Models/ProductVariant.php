<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    protected $fillable = [
        "product_id",
        "name",
        "price",
        "status",
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
        'product_id',
        'status',
    ];

    public $timestamps = false;
}
