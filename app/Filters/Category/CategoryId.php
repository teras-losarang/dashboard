<?php

namespace App\Filters\Category;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class CategoryId
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has("category_id") || (request()->has("category_id") && request()->category_id < 1)) {
            return $next($query);
        }

        $query->where("id", "=", request()->category_id);

        return $next($query);
    }
}
