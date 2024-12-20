<?php

namespace App\Filters\Address;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class AddressId
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has('address_id')) {
            return $next($query);
        }

        $query->where("id", "=", request('address_id'));

        return $next($query);
    }
}
