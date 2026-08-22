<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHasRootUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // The installer manages this state itself — and before installation
        // the DB may not exist at all, so this check must not run there.
        if ($request->is('install', 'install/*')) {
            return $next($request);
        }

        // Check if the application is installed
        try {
            $rootUser = User::role('root')->get();
        } catch (\Throwable) {
            // DB not ready — EnsureInstalled handles routing to the installer.
            return $next($request);
        }
        if(!request()->routeIs(['create.root', 'create.superadmin']) && $rootUser->isEmpty()) {
            return redirect()->route('create.root');
        }
        if(!$rootUser->isEmpty() && request()->routeIs(['create.root', 'create.superadmin'])) {
            return redirect()->route('admin.login')->with('error', 'Super Admin already exists. You cannot create another one.');
        }

        return $next($request);
    }
}
