<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class District extends Model
{
    protected $fillable = ["name", "regency_id"];

    public $timestamps = false;

    public function regency(): HasOne
    {
        return $this->hasOne(Regency::class, "id", "regency_id");
    }
}
