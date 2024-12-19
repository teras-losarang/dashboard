<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Regency extends Model
{
    protected $fillable = ["name", "province_id"];

    public $timestamps = false;

    public function province(): HasOne
    {
        return $this->hasOne(Province::class, "id", "province_id");
    }
}