<?php

namespace App\Filters\Product;

use App\Facades\Helper;
use App\Models\Product;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SortBy
{
    public function handle(Builder $query, Closure $next)
    {
        $sortBy = request('sort_by', 'id');
        $sortDirection = request('sort_direction', 'asc');
        $sortFillable = Helper::onlyFillables([$sortBy => $sortDirection], new Product());

        if (count($sortFillable) < 1) {
            return $next($query);
        }

        $query->orderBy($sortBy, $sortDirection);

        return $next($query);
    }
}
