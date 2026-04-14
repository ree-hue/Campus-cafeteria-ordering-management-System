#!/bin/bash

# Campus Cafeteria PostgreSQL Setup Script
# This script initializes the PostgreSQL database for the application

set -e  # Exit on error

DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-5432}
DB_NAME=${DB_NAME:-campus_cafeteria_ordering_management}
DB_USER=${DB_USER:-campus_cafeteria_user}
DB_PASSWORD=${DB_PASSWORD:-$(echo $RANDOM | md5sum | head -c 16)}

echo "================================================"
echo "Campus Cafeteria - PostgreSQL Setup Script"
echo "================================================"
echo ""

# Check if PostgreSQL is running
if ! command -v psql &> /dev/null; then
    echo "❌ PostgreSQL tools not found. Please install PostgreSQL client."
    echo "   On Ubuntu/Debian: sudo apt-get install postgresql-client"
    echo "   On macOS: brew install postgresql"
    exit 1
fi

echo "📋 Configuration:"
echo "   Host: $DB_HOST"
echo "   Port: $DB_PORT"
echo "   Database: $DB_NAME"
echo "   User: $DB_USER"
echo ""

# Test connection to PostgreSQL
echo "🔍 Testing PostgreSQL connection..."
if PGPASSWORD=$POSTGRES_PASSWORD psql -h "$DB_HOST" -p "$DB_PORT" -U postgres -c "SELECT 1" > /dev/null 2>&1; then
    echo "✅ PostgreSQL connection successful"
else
    echo "⚠️  Could not connect to PostgreSQL. Make sure:"
    echo "   1. PostgreSQL is running"
    echo "   2. Host and port are correct"
    echo "   3. PostgreSQL superuser credentials are available"
    echo ""
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo ""
echo "📦 Setting up database..."

# Create database and user (if superuser access available)
SETUP_SQL="
-- Create user if not exists
DO \$\$ BEGIN
  IF NOT EXISTS (SELECT FROM pg_user WHERE usename = '$DB_USER') THEN
    CREATE USER $DB_USER WITH PASSWORD '$DB_PASSWORD';
  END IF;
END \$\$;

-- Create database if not exists
SELECT 'CREATE DATABASE $DB_NAME OWNER $DB_USER'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = '$DB_NAME')\gexec

-- Grant permissions
GRANT CONNECT ON DATABASE $DB_NAME TO $DB_USER;
GRANT USAGE ON SCHEMA public TO $DB_USER;
GRANT CREATE ON SCHEMA public TO $DB_USER;
GRANT ALL ON ALL TABLES IN SCHEMA public TO $DB_USER;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO $DB_USER;
"

echo "        Creating database and user..."
if PGPASSWORD=$POSTGRES_PASSWORD psql -h "$DB_HOST" -p "$DB_PORT" -U postgres -c "$SETUP_SQL" 2>/dev/null; then
    echo "        ✅ Database created"
else
    echo "        ⚠️  Database creation skipped (requires superuser access)"
fi

echo ""
echo "📊 Importing schema..."

# Import database schema
if PGPASSWORD=$DB_PASSWORD psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -f database.sql > /dev/null 2>&1; then
    echo "✅ Schema imported successfully"
else
    echo "❌ Failed to import schema"
    echo "   Run manually: psql -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME -f database.sql"
    exit 1
fi

echo ""
echo "✨ Setup Complete!"
echo ""
echo "📝 Connection Details:"
echo "   Host: $DB_HOST"
echo "   Port: $DB_PORT"
echo "   Database: $DB_NAME"
echo "   Username: $DB_USER"
echo "   Password: $DB_PASSWORD (save this securely!)"
echo ""
echo "🔐 Set environment variables in your .env file or hosting platform:"
echo ""
echo "   DB_HOST=$DB_HOST"
echo "   DB_PORT=$DB_PORT"
echo "   DB_NAME=$DB_NAME"
echo "   DB_USER=$DB_USER"
echo "   DB_PASSWORD=$DB_PASSWORD"
echo ""
echo "✅ Your application is now ready to use PostgreSQL!"
echo ""
