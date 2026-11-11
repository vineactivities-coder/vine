<?php

namespace App\Http\Middleware;

use App\Services\Seo;
use Closure;
use Illuminate\Http\Request;

class SearchRobots
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (! Seo::indexable($request)) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
