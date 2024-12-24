<?php

namespace App\Filters\Outlet;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class OutletId
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has("outlet_id")) {
            return $next($query);
        }

        $query->where("id", "=", request()->outlet_id);

        return $next($query);
    }
}
