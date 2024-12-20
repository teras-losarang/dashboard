<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'detail',
        'is_default',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'user_id'
    ];

    protected $hidden = [
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'user_id'
    ];

    public function province(): HasOne
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }

    public function regency(): HasOne
    {
        return $this->hasOne(Regency::class, 'id', 'regency_id');
    }

    public function district(): HasOne
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }

    public function village(): HasOne
    {
        return $this->hasOne(Village::class, 'id', 'village_id');
    }
}
