<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ValidateCaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $request->validate(['g-recaptcha-response' => [
            Rule::requiredIf(
                fn() => (getAccessAttempts(attempt: true) > 1 ) && \App::isProduction()
            ), 'captcha'
        ]]);

        return $next($request);
    }
}
