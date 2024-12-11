<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetUserDefaultTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request , Closure $next)
    {
        if ($user = auth()->user()) {
            if($user->timezone){
                config(['app.timezone' => $user->timezone]);
                date_default_timezone_set($user->timezone);
            }
        }

        return $next($request);
    }
}
