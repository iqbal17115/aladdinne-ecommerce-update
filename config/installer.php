<?php

return [

    'name' => 'Laravel Web Installer',

    /*
    |--------------------------------------------------------------------------
    | Seeder run permission here
    |--------------------------------------------------------------------------
    */
    'seeder_run' => true,

    /*
    |--------------------------------------------------------------------------
    | minimum php version
    |--------------------------------------------------------------------------
    */
    'minPhpVersion' => '8.2.0',

    /*
    |--------------------------------------------------------------------------
    | Php and server Requirements
    |--------------------------------------------------------------------------
    | php extensions and apache modules requirements
    */
    'php_extensions' => [
        'mysqli',
        'openssl',
        'pdo',
        'mbstring',
        'JSON',
        'cURL',
        'fileinfo',
        'gmp',
        'xml',
        'zip',
        'sodium',
        'bcMath',
    ],

    /*
    |--------------------------------------------------------------------------
    | Folders Permissions
    |--------------------------------------------------------------------------
    | This is the default Laravel folders permissions, if your application
    | requires more permissions just add them to the array list bellow.
    |
    */
    'permissions' => [
        'storage/' => 777,
        'bootstrap/' => 777,
        'app/Providers/' => 775,
        'routes/' => 775,
        'lang/' => 775,
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Form
    |--------------------------------------------------------------------------
    | environment form fields
    |
    */
    'environment_fields' => [
        [
            'APP_NAME' => [
                'rule' => 'required|string|max:50',
                'label' => 'App name',
                'placeholder' => 'e.g: Web-installer',
                'type' => 'text',
            ],
            'APP_URL' => [
                'rule' => 'required|url',
                'label' => 'App base url',
                'placeholder' => 'e.g: http://example.com',
                'type' => 'text',
            ],
            'APP_DEBUG' => [
                'rule' => 'required|string',
                'label' => 'App debug:',
                'placeholder' => 'Choose app debug mode',
                'option' => [true, false],
                'type' => 'radio',
            ],
        ], [
            'DB_CONNECTION' => [
                'rule' => 'required|string|max:50',
                'label' => 'Database Connection',
                'placeholder' => 'Select Databese',
                'type' => 'select',
                'option' => ['mysql', 'sqlite', 'pgsql', 'sqlsrv'],
                'value' => 'mysql',
            ],
            'DB_HOST' => [
                'rule' => 'required|string|max:50',
                'label' => 'Database Host',
                'type' => 'text',
                'placeholder' => 'e.g: 127.0.0.1',
                'value' => '127.0.0.1',
            ],
            'DB_PORT' => [
                'rule' => 'required|numeric',
                'label' => 'Database Port',
                'type' => 'number',
                'placeholder' => 'e.g: 3306',
                'value' => '3306',
            ],
            'DB_DATABASE' => [
                'rule' => 'required|string|max:50',
                'label' => 'Database Name',
                'type' => 'text',
                'placeholder' => 'e.g: web_installer',
            ],
            'DB_USERNAME' => [
                'rule' => 'required|string|max:50',
                'label' => 'Database Username',
                'type' => 'text',
                'placeholder' => 'e.g: root',
            ],
            'DB_PASSWORD' => [
                'rule' => 'nullable|string|max:50',
                'label' => 'Database Password',
                'type' => 'password',
                'placeholder' => 'e.g: **********',
            ],
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Mendetory items which you want to install
    |--------------------------------------------------------------------------
    */
    'need_to_know' => [
        'Codecanyon Purchase Code',

        'Database Name',
        'Database Username',
        'Database Password',
        'Database Hostname',
        'Database Port',
    ],

    /*
    |--------------------------------------------------------------------------
    | Applications User access
    |--------------------------------------------------------------------------
    */
    'users' => [
        'root' => [
            'name' => 'Joynal Abedin',
            'email' => 'abedin.dev@gmail.com',
            'password' => 'secret',
            'email_verified_at' => now(),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Market place validation
    | set a verification code for active from market
    |--------------------------------------------------------------------------
    */
    'product' => 'Ready Ecommerce', // which product you verify same as supported server
    'verify_code' => 'XUapCS7/OXM6rdPHHyTGYDgvN3VSaXptaG53Zy9wOWJZcVZ4UU1ocmVuMit1UVp1SGRyMFJFeWRoSjRVYVpnazZiNU9JRVJoQTNJTEVOREZ4ZHVZQ1BSdHpaOHJ6dVdCaFZ5QkN3PT0=',
    'productId' => 52519302,
    'admin_role' => 'root',
    'verify_purchase' => true,
    'verify_rules' => [
        'email' => [
            'rule' => 'required|string',
            'label' => 'Your Email',
            'type' => 'email',
            'placeholder' => 'e.g: example@email.com',
        ],
        'purchase_code' => [
            'rule' => 'required|string',
            'label' => 'Purchase Code',
            'type' => 'text',
            'placeholder' => 'e.g: 040afd3f-4cxa-4241-9e70-4gde9e4t674b',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Install commands
    | defind your installation commands
    |--------------------------------------------------------------------------
    */
    'install_commands' => [
        'php artisan migrate:fresh --force',
        'php artisan db:seed --force',
        'php artisan storage:link',
    ],

    /*
    |--------------------------------------------------------------------------
    | Update commands
    | defind your update commands
    |--------------------------------------------------------------------------
    */
    'update_commands' => [
        'composer update --no-interaction',
        'php artisan migrate --force', // if exists any new migration file then it will migrate
        'php artisan cache:clear',
        'php artisan db:Seed PermissionSeeder --force', // if exists any new permission then it will create
        'php artisan db:seed PaymentGatewaySeeder --force', // if exists any new payment gateway then it will create
    ],

    /*
    |--------------------------------------------------------------------------
    | Secure restore writes
    |--------------------------------------------------------------------------
    | License verification restore payloads may update files only inside these
    | directories and only for the listed extensions.
    */
    'restore' => [
        'allowed_directories' => [
            'app',
            'bootstrap',
            'config',
            'database',
            'public',
            'resources',
            'routes',
        ],
        'allowed_extensions' => ['php', 'json'],
    ],

    'regular_license' => [
        'link' => 'https://codecanyon.net/checkout/from_item/52519302/?license=regular&size=source&ref=pds',
        'price' => 39,
    ],
    'extende_license' => [
        'link' => 'https://codecanyon.net/checkout/from_item/52519302/?license=extended&size=source&ref=pds',
        'price' => 199,
    ],
];
