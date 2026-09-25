#!/bin/bash
# VPS Deployment Script for Ai Benchmark Hub
# Run this on your VPS after cloning the repository

set -e

echo "=== Ai Benchmark Hub Deployment ==="

# 1. Install dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# 2. Install and build frontend
echo "Building frontend assets..."
npm ci
npm run build

# 3. Configure environment
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
    php artisan key:generate
    echo "Please configure your .env file with database credentials and API keys."
    exit 1
fi

# 4. Run migrations
echo "Running migrations..."
php artisan migrate --force

# 5. Seed database (first deploy only)
echo "Seeding database..."
php artisan db:seed --force

# 6. Cache configuration
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 8. Setup queue worker (systemd service)
echo "Setting up queue worker..."
cat > /etc/systemd/system/benchmark-worker.service << 'SERVICEEOF'
[Unit]
Description=Ai Benchmark Hub Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/benchmark
ExecStart=/usr/bin/php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
SERVICEEOF

systemctl daemon-reload
systemctl enable benchmark-worker
systemctl start benchmark-worker

echo "=== Deployment complete! ==="
echo "Configure your web server (Nginx/Apache) to serve from the 'public' directory."
echo "Remember to set up:"
echo "  - Database credentials in .env"
echo "  - OpenAI API key (for scoring engine)"
echo "  - SSL certificate (recommended: Let's Encrypt)"
