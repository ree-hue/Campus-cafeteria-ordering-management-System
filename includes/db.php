<?php
/**
 * PostgreSQL Database Connection
 * For Render.com Deployment
 */

// Database configuration
$config = [
    'host' => getenv('DB_HOST') ?: 'dpg-d7f8shi8qa3s73d52hng-a.oregon-postgres.render.com',
    'port' => getenv('DB_PORT') ?: '5432',
    'dbname' => getenv('DB_NAME') ?: 'campus_cafeteria_ordering_management',
    'user' => getenv('DB_USER') ?: 'campus_cafeteria_ordering_management_user',
    'password' => getenv('DB_PASSWORD') ?: 'ZlkQbgwJCW0FJUA9nXYglDm0VGnGPhLj'
];

// Build connection string
$conn_string = sprintf(
    "host=%s port=%s dbname=%s user=%s password=%s",
    $config['host'],
    $config['port'],
    $config['dbname'],
    $config['user'],
    $config['password']
);

// Connect to PostgreSQL
$conn = pg_connect($conn_string);

// Check connection
if (!$conn) {
    // Log error for debugging
    error_log("PostgreSQL connection failed: " . pg_last_error());
    
    // Show user-friendly message
    die("Unable to connect to database. Please try again later.");
}

// Set UTF-8 encoding
pg_set_client_encoding($conn, "UTF8");

// Uncomment for debugging only
// echo "✅ Connected to: " . $config['dbname'];
?>