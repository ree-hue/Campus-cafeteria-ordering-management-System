<?php
// PostgreSQL connection for Campus Cafeteria System
// Supports both local development and cloud deployment (Render, Aiven, etc)

// Include error handler
require_once 'error_handler.php';

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'campus_cafeteria_ordering_management';
$user = getenv('DB_USER') ?: 'postgres';
$pass = getenv('DB_PASSWORD') ?: '';

// Create PostgreSQL connection string with SSL for cloud databases
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$pass sslmode=prefer";

// Connect to PostgreSQL
$conn = @pg_connect($conn_string);

// Check connection
if (!$conn) {
    $error_msg = "Unable to connect to PostgreSQL database.\n";
    $error_msg .= "Host: " . safeOutput($host) . "\n";
    $error_msg .= "Port: " . safeOutput($port) . "\n";
    $error_msg .= "Database: " . safeOutput($dbname) . "\n";
    $error_msg .= "User: " . safeOutput($user) . "\n\n";
    $error_msg .= "Please check your database credentials and firewall settings.\n";
    $error_msg .= "Ensure this server IP is whitelisted in your database firewall.";
    
    error_log("PostgreSQL Connection Failed: " . $error_msg);
    die("<h2>Database Connection Failed</h2><pre>" . htmlspecialchars($error_msg) . "</pre>");
}

// Set timezone and encoding
pg_query($conn, "SET TIMEZONE = 'UTC'");
pg_set_client_encoding($conn, "UTF8");

?>
