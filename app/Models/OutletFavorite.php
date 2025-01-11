<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class OutletFavorite extends Model
{
    use HasUlids;

    protected $fillable = ["outlet_id", "user_id"];
}
