<?php

namespace App\Http\Middleware;

use App\Support\InstallState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    /**
     * Redirect every web request to the installer until the app is installed.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('install', 'install/*', 'up', 'api/*')) {
            return $next($request);
        }

        if (InstallState::isInstalled()) {
            return $next($request);
        }

        return redirect('/install');
    }
}
