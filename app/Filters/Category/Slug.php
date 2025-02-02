<?php

namespace App\Filters\Category;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class Slug
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has("slug") || (request()->has("slug") && !request()->slug)) {
            return $next($query);
        }

        $query->where("slug", "=", request()->slug);

        return $next($query);
    }
}
