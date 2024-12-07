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

    public $timestamps = false;
}
