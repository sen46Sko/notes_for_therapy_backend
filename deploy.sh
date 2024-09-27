#!/bin/bash

# Zip current folder
zip -r deploy.zip . -x "*.git*" -x "*node_modules*" -x "*deploy.sh" -x "*vendor*" -x "*.vercel*"

# Cleanup previous deployment
ssh -o "IdentitiesOnly yes" -i KateLaravelSSH.pem ubuntu@3.124.219.120 << 'ENDSSH'

cd /var/www/KateLaravel

# Remove all files in current directory, except database/migrations
sudo rm -rf /var/www/KateLaravel/*

ENDSSH

# Secure copy to endpoint in /var/www/KateLaravel folder using SSH key KateLaravelSSH.pem
scp -o "IdentitiesOnly yes" -i KateLaravelSSH.pem deploy.zip ubuntu@3.124.219.120:/var/www/KateLaravel

# Connect to endpoint
ssh -o "IdentitiesOnly yes" -i KateLaravelSSH.pem ubuntu@3.124.219.120 << 'ENDSSH'

cd /var/www/KateLaravel

# Unzip
unzip -o deploy.zip


sudo chown -R www-data:www-data /var/www/KateLaravel/storage

sudo chmod -R 777 /var/www/KateLaravel/storage

# Remove zip
rm deploy.zip
#
# source ~/.nvm/nvm.sh

# Install dependencies
# npm install

# Install/update composer dependencies
# composer install --no-dev --prefer-dist

composer update

# Run database migrations
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate new application key
# php artisan key:generate

ENDSSH

# Remove deploy.zip
rm deploy.zip
