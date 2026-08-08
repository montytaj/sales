<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale') ?? $request->segment(1);

        if (in_array($locale, ['ar', 'en'])) {
            App::setLocale($locale);
            if (class_exists(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::class)) {
                \Mcamara\LaravelLocalization\Facades\LaravelLocalization::setLocale($locale);
            }
            session(['locale' => $locale]);
        }

        return $next($request);
    }
}
