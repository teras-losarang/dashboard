<?php

namespace App\Filters\Product;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class ProductId
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has("product_id") || (request()->has("product_id") && request()->product_id < 1)) {
            return $next($query);
        }

        $query->where("id", "=", request()->product_id);

        return $next($query);
    }
}
