#!/bin/bash
# First-time deploy script for Oracle VM
# Run this ON the VM after cloning the repo

set -e

cd ~/forno-celeste

# Create production .env
cp .env.example .env

# Generate secure passwords
DB_PASS=$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 20)
DB_ROOT_PASS=$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 20)

# Configure .env for production
sed -i "s|APP_ENV=local|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=true|APP_DEBUG=false|" .env
sed -i "s|APP_URL=http://localhost:8080|APP_URL=http://$(curl -s ifconfig.me)|" .env
sed -i "s|DB_PASSWORD=secret|DB_PASSWORD=$DB_PASS|" .env
sed -i "s|DB_ROOT_PASSWORD=rootsecret|DB_ROOT_PASSWORD=$DB_ROOT_PASS|" .env
sed -i "s|APP_PORT=8080|APP_PORT=80|" .env

echo ""
echo "=== .env configured ==="
echo "DB_PASSWORD=$DB_PASS"
echo "DB_ROOT_PASSWORD=$DB_ROOT_PASS"
echo "SAVE THESE PASSWORDS SOMEWHERE SAFE"
echo ""

# Build and start containers
docker compose up -d --build

# Wait for DB to be healthy
echo "Waiting for database..."
sleep 15

# Install dependencies
docker compose exec -T app composer install --no-dev --optimize-autoloader

# Generate app key
docker compose exec -T app php artisan key:generate --force

# Run migrations
docker compose exec -T app php artisan migrate --force

# Import database dump if exists
if [ -f ~/dump.sql ]; then
    echo "Importing database dump..."
    docker compose exec -T db mysql -u root -p"$DB_ROOT_PASS" fornoceleste < ~/dump.sql
    echo "Database imported!"
fi

# Build frontend assets
docker compose run --rm node sh -c "npm ci && npm run build"

# Optimize Laravel
docker compose exec -T app php artisan optimize
docker compose exec -T app php artisan view:cache

echo ""
echo "=== Deploy complete! ==="
echo "Access: http://$(curl -s ifconfig.me)"
