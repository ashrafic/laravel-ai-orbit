<?php

namespace Ashrafic\AiOrbit\Http\Middleware;

use Ashrafic\AiOrbit\Support\OrbitConfig;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Authorize
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::guard(OrbitConfig::guard())->user();

        if (! Gate::check('viewAiOrbit', [$user])) {
            abort(403);
        }

        return $next($request);
    }
}
