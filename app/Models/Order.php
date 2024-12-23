<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        "user_id",
        "outlet_id",
        "name",
        "phone",
        "address_user",
        "address_outlet",
        "total",
        "created_by",
        "status"
    ];

    protected $hidden = [
        "user_id",
        "outlet_id",
        "updated_at",
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, "order_id", "id");
    }
}
