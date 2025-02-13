#!/bin/bash
set -e

echo "Starting pgsql-init.sh script with environment substitution"

# Install envsubst if it's not already installed
if ! command -v envsubst >/dev/null; then
  echo "envsubst not found, installing gettext-base..."
  apt-get update && apt-get install -y gettext-base
fi

# Create the database and user with environment variables
envsubst <<'EOF' | psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname postgres
-- Create the database if it doesn't already exist
SELECT 'CREATE DATABASE "$DB_NAME";'
WHERE NOT EXISTS (
  SELECT FROM pg_database WHERE datname = '$DB_NAME'
) \gexec

-- Create the user if it doesn't exist already
DO \$\$
BEGIN
  IF NOT EXISTS (
    SELECT FROM pg_roles WHERE rolname = '$DB_USER'
  ) THEN
    EXECUTE 'CREATE USER "$DB_USER" WITH PASSWORD ''$DB_PASSWORD''';
  END IF;
END
\$\$;

-- Grant all privileges on the new database to the user
GRANT ALL PRIVILEGES ON DATABASE "$DB_NAME" TO "$DB_USER";
EOF

echo "pgsql-init.sh script completed"
