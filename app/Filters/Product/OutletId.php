<?php

namespace App\Filters\Product;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class OutletId
{
    public function handle(Builder $query, Closure $next)
    {
        $query->whereHas("outlet", function ($query) {
            $query->where("status", 1);
        });

        if (!request()->has("outlet_id") || (request()->has("outlet_id") && request()->outlet_id < 1)) {
            return $next($query);
        }

        $query->where("outlet_id", "=", request()->outlet_id);

        return $next($query);
    }
}
