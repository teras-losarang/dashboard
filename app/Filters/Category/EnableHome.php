<?php

namespace App\Filters\Category;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class EnableHome
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has("enable_home")) {
            return $next($query);
        }

        $query->where("enable_home", "=", request()->enable_home);

        return $next($query);
    }
}
