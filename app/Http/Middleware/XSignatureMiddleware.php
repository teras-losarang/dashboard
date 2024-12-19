<?php

namespace App\Http\Middleware;

use App\Facades\MessageFixer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XSignatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('X-Signature') || !$request->hasHeader('X-Timestamp')) {
            return MessageFixer::error("Invalid signature!");
        }

        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');

        $currentTime = time();
        if (abs($currentTime - $timestamp) > 300) {
            return MessageFixer::error("Invalid signature!");
        }

        $secretKey = env("SECRET_API");
        $path = str_replace("api/", "", $request->path());
        $params = http_build_query([
            "method" => $request->method(),
            "path" => $path,
            "timestamp" => $timestamp
        ]);
        $params = base64_encode($params);
        $expectedSignature = hash_hmac("sha256", $params, $secretKey);

        if (!hash_equals($signature, $expectedSignature)) {
            return MessageFixer::error("Invalid signature!");
        }

        return $next($request);
    }
}
