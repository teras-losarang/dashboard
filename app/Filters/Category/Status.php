<?php

namespace App\Filters\Category;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class Status
{
    public function handle(Builder $query, Closure $next)
    {
        $query->where("status", "=", 1);
        return $next($query);
    }
}
