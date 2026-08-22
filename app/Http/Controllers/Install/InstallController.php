<?php

namespace App\Http\Controllers\Install;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use App\Models\Wallet;
use App\Support\InstallState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstallController extends Controller
{
    protected const MIN_PHP = '8.4.0';

    protected const REQUIRED_EXTENSIONS = [
        'pdo_mysql', 'openssl', 'mbstring', 'curl', 'gd', 'fileinfo', 'json',
    ];

    /**
     * Seeders required for the app to function (mirrors DatabaseSeeder's
     * always-run list). Each is guarded by class_exists at run time.
     */
    protected const ESSENTIAL_SEEDERS = [
        'RoleSeeder', 'PermissionSeeder', 'CurrencySeeder', 'GeneraleSettingSeeder',
        'LegalPageSeeder', 'PaymentGatewaySeeder', 'SocialLinkSeeder', 'ThemeColorSeeder',
        'SocialAuthSeeder', 'VerifyManageSeeder', 'PageSeeder', 'MenuSeeder',
        'CountrySeeder', 'FooterSeeder', 'SupportItemSeeder', 'CatalogSlugSeeder',
    ];

    /** Optional demo-content seeders (mirrors DatabaseSeeder's local list). */
    protected const DEMO_SEEDERS = [
        'UserSeeder', 'CustomerSeeder', 'RiderSeeder', 'ShopSeeder', 'CategorySeeder',
        'BrandSeeder', 'SizeSeeder', 'ColorSeeder', 'UnitSeeder', 'AreaSeeder',
        'ProductSeeder', 'BannerSeeder', 'CouponSeeder', 'AddressSeeder', 'OrderSeeder',
        'ReviewSeeder', 'FavoriteSeeder', 'BlogSeeder', 'RootAdminShopSeeder',
        'ContactUsSeeder', 'WalletSeeder',
    ];

    public function requirements()
    {
        if (InstallState::isInstalled()) {
            return redirect()->route('admin.login');
        }

        $checks = $this->requirementChecks();
        $tokenVerified = $this->tokenVerified();

        // Ensure the token file exists so the operator can read it off the
        // server before entering it below. (No-op once already verified.)
        if (! $tokenVerified) {
            $this->installToken();
        }

        return view('install.requirements', [
            'checks' => $checks,
            'allPassed' => collect($checks)->every(fn ($check) => $check['passed']),
            'tokenVerified' => $tokenVerified,
            'tokenPath' => $this->tokenPath(),
        ]);
    }

    /**
     * Verify the one-time install token. The token is written to a file on the
     * server that only someone with filesystem access (SSH / cPanel / file
     * manager) can read — this proves the request comes from the legitimate
     * operator, not an anonymous visitor racing to the public installer.
     */
    public function verifyToken(Request $request)
    {
        if (InstallState::isInstalled()) {
            return redirect()->route('admin.login');
        }

        $request->validate(['install_token' => ['required', 'string']]);

        if (! hash_equals($this->installToken(), trim($request->input('install_token')))) {
            return back()->withErrors(['install_token' => 'Invalid install token.']);
        }

        $request->session()->put('install_token_ok', true);

        return redirect()->route('install.database');
    }

    public function database()
    {
        if (InstallState::isInstalled()) {
            return redirect()->route('admin.login');
        }

        if ($guard = $this->guardToken()) {
            return $guard;
        }

        if (! collect($this->requirementChecks())->every(fn ($check) => $check['passed'])) {
            return redirect()->route('install.requirements');
        }

        return view('install.database');
    }

    public function saveDatabase(Request $request)
    {
        if (InstallState::isInstalled()) {
            return redirect()->route('admin.login');
        }

        if ($guard = $this->guardToken()) {
            return $guard;
        }

        $data = $request->validate([
            'app_url' => ['required', 'url'],
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'numeric'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
        ]);

        // Test the connection on a throwaway connection BEFORE saving anything.
        config(['database.connections.install' => [
            'driver' => 'mysql',
            'host' => $data['db_host'],
            'port' => $data['db_port'],
            'database' => $data['db_database'],
            'username' => $data['db_username'],
            'password' => $data['db_password'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        try {
            DB::purge('install');
            DB::connection('install')->getPdo();
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'db_database' => 'Could not connect to the database: '.$e->getMessage(),
            ]);
        }

        $this->writeEnv([
            'APP_URL' => rtrim($data['app_url'], '/'),
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
        ]);

        if (empty(config('app.key')) && empty(env('APP_KEY'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        // Make sure the next request reads the fresh .env values.
        Artisan::call('config:clear');

        return redirect()->route('install.migrate');
    }

    public function migrate()
    {
        if (InstallState::isInstalled()) {
            return redirect()->route('admin.login');
        }

        if ($guard = $this->guardToken()) {
            return $guard;
        }

        return view('install.migrate');
    }

    public function runMigrate(Request $request)
    {
        if (InstallState::isInstalled()) {
            return redirect()->route('admin.login');
        }

        if ($guard = $this->guardToken()) {
            return $guard;
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            return back()->withErrors(['migrate' => 'Migration failed: '.$e->getMessage()]);
        }

        $warnings = $this->runSeeders(self::ESSENTIAL_SEEDERS);

        if ($request->boolean('demo_data')) {
            $warnings = array_merge($warnings, $this->runSeeders(self::DEMO_SEEDERS));
        }

        return redirect()->route('install.admin')
            ->with('seed_warnings', $warnings);
    }

    public function admin()
    {
        if (InstallState::isInstalled()) {
            return redirect()->route('admin.login');
        }

        if ($guard = $this->guardToken()) {
            return $guard;
        }

        return view('install.admin');
    }

    public function saveAdmin(Request $request)
    {
        if (InstallState::isInstalled()) {
            return redirect()->route('admin.login');
        }

        if ($guard = $this->guardToken()) {
            return $guard;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $this->availablePhone($data['email']),
                    'password' => Hash::make($data['password']),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole(Roles::ROOT->value);

            Shop::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => 'My Shop',
                    'delivery_charge' => 0,
                    'description' => 'My Shop Description',
                    'status' => true,
                    'min_order_amount' => 1,
                ]
            );

            $user->assignRole(Roles::SHOP->value);

            if (class_exists(Wallet::class)) {
                Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
            }
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'email' => 'Could not create the admin: '.$e->getMessage(),
            ]);
        }

        InstallState::markInstalled();

        // Installation is complete — invalidate the one-time install token so
        // the (now redundant) installer can never be re-driven with it.
        @unlink($this->tokenPath());
        $request->session()->forget('install_token_ok');

        Artisan::call('optimize:clear');

        return redirect()->route('install.finished');
    }

    /**
     * Path to the one-time install token file. Kept out of the web root and
     * only readable by someone with server filesystem access.
     */
    protected function tokenPath(): string
    {
        return storage_path('app/install-token.txt');
    }

    /**
     * Return the install token, generating and persisting it on first call.
     */
    protected function installToken(): string
    {
        $path = $this->tokenPath();

        if (! is_file($path)) {
            $token = bin2hex(random_bytes(16));
            @file_put_contents($path, $token);
            @chmod($path, 0600);

            return $token;
        }

        return trim((string) file_get_contents($path));
    }

    protected function tokenVerified(): bool
    {
        return session('install_token_ok') === true;
    }

    /**
     * Redirect back to the token gate unless it has been passed this session.
     */
    protected function guardToken(): ?\Illuminate\Http\RedirectResponse
    {
        if ($this->tokenVerified()) {
            return null;
        }

        return redirect()->route('install.requirements')
            ->withErrors(['install_token' => 'Enter the install token to continue.']);
    }

    public function finished()
    {
        if (! InstallState::isInstalled()) {
            return redirect()->route('install.requirements');
        }

        return view('install.finished');
    }

    /**
     * Users.phone is unique — pick a free default without clashing with
     * seeded demo users.
     */
    protected function availablePhone(string $email): string
    {
        $existing = User::where('email', $email)->value('phone');
        if ($existing) {
            return $existing;
        }

        $phone = '01000000001';
        while (User::where('phone', $phone)->exists()) {
            $phone = '01'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        }

        return $phone;
    }

    /** @return array<int, string> warnings for seeders that failed */
    protected function runSeeders(array $seeders): array
    {
        $warnings = [];

        foreach ($seeders as $seeder) {
            $class = 'Database\\Seeders\\'.$seeder;

            if (! class_exists($class)) {
                continue;
            }

            try {
                Artisan::call('db:seed', ['--class' => $class, '--force' => true]);
            } catch (\Throwable $e) {
                $warnings[] = $seeder.': '.$e->getMessage();
            }
        }

        return $warnings;
    }

    protected function requirementChecks(): array
    {
        $checks = [
            [
                'label' => 'PHP >= '.self::MIN_PHP.' (current: '.PHP_VERSION.')',
                'passed' => version_compare(PHP_VERSION, self::MIN_PHP, '>='),
            ],
        ];

        foreach (self::REQUIRED_EXTENSIONS as $extension) {
            $checks[] = [
                'label' => 'PHP extension: '.$extension,
                'passed' => extension_loaded($extension),
            ];
        }

        $checks[] = ['label' => 'storage/ is writable', 'passed' => is_writable(storage_path())];
        $checks[] = ['label' => 'bootstrap/cache is writable', 'passed' => is_writable(base_path('bootstrap/cache'))];
        $checks[] = [
            'label' => '.env exists and is writable',
            'passed' => is_file(base_path('.env')) && is_writable(base_path('.env')),
        ];

        return $checks;
    }

    /**
     * Update-or-append KEY=value lines in .env.
     */
    protected function writeEnv(array $pairs): void
    {
        $path = base_path('.env');
        $env = is_file($path) ? file_get_contents($path) : '';

        foreach ($pairs as $key => $value) {
            $value = (string) $value;

            if (preg_match('/\s/', $value) || str_contains($value, '#')) {
                $value = '"'.addcslashes($value, '"\\').'"';
            }

            $line = $key.'='.$value;

            if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $env)) {
                $env = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $env);
            } else {
                $env .= (str_ends_with($env, "\n") || $env === '' ? '' : "\n").$line."\n";
            }
        }

        file_put_contents($path, $env);
    }
}
