<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class PrepareInstallEnvironment
{
    /**
     * Fresh drop-ins may arrive without a .env (only .env.example) or with an
     * empty APP_KEY — both 500 before the installer can render (EncryptCookies
     * needs a key). Runs as GLOBAL middleware, before the web group.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*') || config('app.key')) {
            return $next($request);
        }

        $envPath = base_path('.env');

        if (! is_file($envPath) && is_file(base_path('.env.example'))) {
            @copy(base_path('.env.example'), $envPath);
        }

        if (is_file($envPath) && is_writable($envPath)) {
            $env = (string) file_get_contents($envPath);

            // Only generate when the file itself has no key; the runtime
            // config being empty on the very next request is expected.
            if (preg_match('/^APP_KEY=\s*$/m', $env) || ! str_contains($env, 'APP_KEY=')) {
                try {
                    Artisan::call('key:generate', ['--force' => true]);
                } catch (\Throwable) {
                    return $next($request);
                }

                // Fresh request so the new key is actually loaded.
                return redirect($request->fullUrl());
            }
        }

        return $next($request);
    }
}
