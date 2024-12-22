<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, "order_id", "id");
    }
}
