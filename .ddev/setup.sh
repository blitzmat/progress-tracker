#!/bin/bash

set -e

echo "🚀 Running Laravel setup..."

# Install dependencies if vendor missing
if [ ! -d "vendor" ]; then
  composer install
fi

# Env setup
if [ ! -f ".env" ]; then
  cp .env.example .env
  php artisan key:generate
fi

# Run migrations only if DB exists
php artisan migrate --force || true

if [ ! -f ".ddev/.initialized" ]; then
    echo "First-time setup..."
    # Storage link
    php artisan storage:link || true
    touch .ddev/.initialized
fi

# Install node dependencies if node_modules missing
if [ ! -d "node_modules" ]; then
  npm install
fi
npm run build

echo "✅ Setup complete"
