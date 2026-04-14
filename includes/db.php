<?php
// PostgreSQL connection
$host = 'pg-32304d1d-almadonald8-d144.e.aivencloud.com';
$port = '16350';
$dbname = 'defaultdb';
$user = 'avnadmin';
$pass = 'YOUR_AIVEN_PASSWORD_HERE';  // Put your actual password

// Create PostgreSQL connection string with SSL
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$pass sslmode=require";

// Connect to PostgreSQL
$conn = pg_connect($conn_string);

// Check connection
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// echo "Connected successfully to PostgreSQL!";
?>
