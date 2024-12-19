<?php

namespace App\Filters\Product;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class Tags
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has("tags")) {
            return $next($query);
        }

        $query->whereHas('categories', function (Builder $query) {
            $query->where('name', 'like', '%' . request()->tags . '%');
        });

        return $next($query);
    }
}
