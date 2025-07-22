#!/bin/bash
set -e

# Check if we're in local environment and start Vite if needed
if [ "$APP_ENV" = "local" ]; then
    echo "Starting Vite in background (local environment)..."
    cd /var/www/app && npm run dev &
fi

# Start supervisor
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf 