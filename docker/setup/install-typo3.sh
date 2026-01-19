#!/bin/bash
set -e

echo "Starting TYPO3 E2E test environment setup..."

# Wait for database to be ready with better retry logic
echo "Waiting for database to be ready..."
echo "Host: $TYPO3_DB_HOST, User: $TYPO3_DB_USER, Database: $TYPO3_DB_NAME"
MAX_TRIES=60
COUNTER=0

while [ $COUNTER -lt $MAX_TRIES ]; do
    COUNTER=$((COUNTER + 1))

    # Try to connect and run a simple query (skip SSL for local development)
    if mysql -h"$TYPO3_DB_HOST" -u"$TYPO3_DB_USER" -p"$TYPO3_DB_PASSWORD" "$TYPO3_DB_NAME" --skip-ssl -e "SELECT 1" >/dev/null 2>&1; then
        echo "✓ Database connection successful!"
        break
    fi

    if [ $COUNTER -ge $MAX_TRIES ]; then
        echo "✗ ERROR: Database did not become ready in time (tried $MAX_TRIES times)"
        echo "Attempting manual connection test for debugging..."
        mysql -h"$TYPO3_DB_HOST" -u"$TYPO3_DB_USER" -p"$TYPO3_DB_PASSWORD" "$TYPO3_DB_NAME" -e "SELECT 1"
        exit 1
    fi

    if [ $((COUNTER % 5)) -eq 0 ]; then
        echo "  Still waiting for database... (attempt $COUNTER/$MAX_TRIES)"
    fi
    sleep 2
done

# Check if TYPO3 is already installed
if [ ! -f "/var/www/html/config/system/settings.php" ]; then
    echo "Installing TYPO3..."

    # Create necessary directories
    mkdir -p /var/www/html/config/system
    mkdir -p /var/www/html/var

    # Run TYPO3 setup command
    vendor/bin/typo3 setup \
        --no-interaction \
        --force \
        --driver=mysqli \
        --host="$TYPO3_DB_HOST" \
        --port=3306 \
        --dbname="$TYPO3_DB_NAME" \
        --username="$TYPO3_DB_USER" \
        --password="$TYPO3_DB_PASSWORD" \
        --admin-username="${TYPO3_ADMIN_USERNAME:-admin}" \
        --admin-user-password="${TYPO3_ADMIN_PASSWORD:-Admin123!}" \
        --admin-email="admin@example.com" \
        --project-name="typo3-e2e" \
        --server-type="apache" \
        --create-site="http://localhost:8080/"

    echo "TYPO3 installed successfully!"
else
    echo "TYPO3 is already installed, skipping installation..."
fi

# Install the simple_rest_api extension
if [ -d "/var/www/html/extensions/simple_rest_api" ]; then
    echo "Setting up simple_rest_api extension..."

    # Create symlink if not exists
    if [ ! -L "/var/www/html/public/typo3conf/ext/simple_rest_api" ]; then
        mkdir -p /var/www/html/public/typo3conf/ext
        ln -sf /var/www/html/extensions/simple_rest_api /var/www/html/public/typo3conf/ext/simple_rest_api
    fi

    # Add extension to composer repositories
    composer config repositories.simple_rest_api path /var/www/html/extensions/simple_rest_api

    # Require the extension via composer
    composer require queo/simple-rest-api:"@dev" --no-interaction || echo "Extension already required"

    # Setup extension
    vendor/bin/typo3 extension:setup simple_rest_api || true

    echo "Extension set up!"
fi

# Create site configuration with route enhancer
echo "Setting up site configuration..."
mkdir -p /var/www/html/config/sites/main

cat > /var/www/html/config/sites/main/config.yaml <<EOF
rootPageId: 1
base: 'http://localhost:8080/'
languages:
  - languageId: 0
    title: English
    enabled: true
    base: /
    locale: en_US.UTF-8
    navigationTitle: English
    flag: us

imports:
  - { resource: "EXT:simple_rest_api/Configuration/Yaml/RouteEnhancer.yaml" }

settings:
  simple_rest_api:
    basePath: '/api/'
EOF

echo "Site configuration created!"

# Flush caches
echo "Flushing caches..."
vendor/bin/typo3 cache:flush || true

echo "TYPO3 E2E test environment is ready!"
echo "Access TYPO3 at: http://localhost:8080"
echo "API endpoints available at: http://localhost:8080/api/test/*"
