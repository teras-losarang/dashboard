<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Village extends Model
{
    protected $fillable = ["name", "district_id"];

    public $timestamps = false;

    public function district(): HasOne
    {
        return $this->hasOne(District::class, "id", "district_id");
    }
}
