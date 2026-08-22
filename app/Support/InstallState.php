<?php

namespace App\Support;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstallState
{
    /**
     * Single source of truth for "is this application installed?".
     *
     * Installed means: the database is reachable AND the users table exists
     * AND at least one root admin exists. The lock file is only a fast cache
     * we control — never proof by itself.
     */
    public static function isInstalled(): bool
    {
        if (is_file(self::lockPath())) {
            if (self::hasRootAdmin()) {
                return true;
            }

            // Stale/mis-placed lock (copied project, half install) — remove it
            // so the request is sent to the installer instead of 500-ing.
            @unlink(self::lockPath());

            return false;
        }

        if (self::hasRootAdmin()) {
            self::markInstalled();

            return true;
        }

        return false;
    }

    public static function markInstalled(): void
    {
        @file_put_contents(self::lockPath(), date('Y-m-d H:i:s'));
    }

    public static function lockPath(): string
    {
        return storage_path('installed');
    }

    /**
     * A real install has a reachable DB with at least one root-role user.
     */
    protected static function hasRootAdmin(): bool
    {
        try {
            if (! Schema::hasTable('users') || ! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
                return false;
            }

            return DB::table('users')
                ->join('model_has_roles', function ($join) {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.model_type', User::class);
                })
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', Roles::ROOT->value)
                ->exists();
        } catch (\Throwable) {
            // DB not configured / unreachable — never trust a bare lock file.
            return false;
        }
    }
}
