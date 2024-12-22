<?php

namespace App\Filters\Order;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class OrderId
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has('order_id')) {
            return $next($query);
        }

        $query->where("id", "=", request('order_id'));

        return $next($query);
    }
}
