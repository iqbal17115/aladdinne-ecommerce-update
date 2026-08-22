#!/bin/sh
set -e

echo "Deploying application ..."

# Enter maintenance mode
php artisan down
    # Install dependencies based on lock file
    # composer update --no-interaction

    # Migrate database
    php artisan migrate --force

    # Note: If you're using queue workers, this is the place to restart them.

    sudo chmod -R 777 storage

    # Run database seeder
    # php artisan db:seed PageSeeder --force
    # php artisan db:seed MenuSeeder --force
    # php artisan db:seed CountrySeeder --force
    # php artisan db:seed PermissionSeeder --force
    # php artisan db:seed FooterSeeder --force
    # php artisan db:seed RiderSeeder --force
    # php artisan db:seed FooterSeeder --force

    # Clear cache
    # php artisan route:clear
    # php artisan config:cache

    # Compile assets
    # npm update --no-interaction
    npm run build --no-interaction

# Exit maintenance mode
php artisan up

echo "Application deployed!"
