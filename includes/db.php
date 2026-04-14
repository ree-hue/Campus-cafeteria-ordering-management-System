<?php
// PostgreSQL connection for Campus Cafeteria System
// Supports both local development and Render deployment

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'campus_cafeteria_ordering_management';
$user = getenv('DB_USER') ?: 'postgres';
$pass = getenv('DB_PASSWORD') ?: '';

// Create PostgreSQL connection string with SSL for Render
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$pass sslmode=prefer";

// Connect to PostgreSQL
$conn = pg_connect($conn_string);

// Check connection
if (!$conn) {
    error_log("PostgreSQL Connection Error: " . pg_last_error());
    die("<h2>Database Connection Failed</h2>" . 
        "<p>Error: " . htmlspecialchars(pg_last_error()) . "</p>" .
        "<p>Please check database credentials and ensure PostgreSQL is running.</p>");
}

// Set timezone and encoding
pg_query($conn, "SET TIMEZONE = 'UTC'");
pg_set_client_encoding($conn, "UTF8");

?>