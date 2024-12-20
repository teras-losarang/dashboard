<?php

namespace App\Filters\Address;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class Auth
{
    public function handle(Builder $query, Closure $next)
    {
        $query->where("user_id", "=", FacadesAuth::user()->id);

        return $next($query);
    }
}
