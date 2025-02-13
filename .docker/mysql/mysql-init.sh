#!/bin/bash
set -e

echo "Starting mysql-init.sh script with environment substitution"

# Install envsubst if it's not already installed
if ! command -v envsubst >/dev/null; then
  echo "envsubst not found, installing gettext-base..."
  apt-get update && apt-get install -y gettext-base
fi

# Create the database and user with environment variables
envsubst <<'EOF' | mysql -u root -p"${MYSQL_ROOT_PASSWORD}"
-- Create database using environment variables
CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user and grant privileges
CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
EOF

echo "mysql-init.sh script completed"
