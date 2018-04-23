<?php

namespace App\Http\Middleware;
use Closure;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->cookie('password') !== env('APP_PASSWORD')) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
