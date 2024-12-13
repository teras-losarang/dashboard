<?php

namespace App\Filters\Category;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class Type
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has("type") || (request()->has("type") && request()->type < 1)) {
            return $next($query);
        }

        $query->where("type", "=", request()->type);

        return $next($query);
    }
}
