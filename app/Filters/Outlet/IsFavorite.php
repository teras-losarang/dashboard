<?php

namespace App\Filters\Outlet;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class IsFavorite
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has("is_favorite") || (request()->has("is_favorite") && request()->is_favorite == 0)) {
            return $next($query);
        }

        $query->whereHas('favorite', function (Builder $query) {
            $query->where('user_id', '=', request()->user()->id);
        });

        return $next($query);
    }
}
