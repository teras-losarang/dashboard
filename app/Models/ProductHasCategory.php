<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductHasCategory extends Model
{
    protected $fillable = ["product_id", "category_id"];

    public $timestamps = false;
}
