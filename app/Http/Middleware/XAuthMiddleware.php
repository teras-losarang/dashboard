<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class XAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('X-Auth')) {
            return redirect()->route('login');
        }

        $tokens = explode("|", $request->header('X-Auth'));
        $personalAccessToken = PersonalAccessToken::findToken($request->header('X-Auth'));

        if (!$personalAccessToken) {
            return redirect()->route('login');
        }

        if ($personalAccessToken && $personalAccessToken->id != $tokens[0]) {
            return redirect()->route('login');
        }

        Auth::login($personalAccessToken->tokenable);

        return $next($request);
    }
}
