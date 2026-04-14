<?php
// Test PostgreSQL connection
$host = 'pg-32304d1d-almadonald8-d144.e.aivencloud.com';
$port = '16350';
$dbname = 'defaultdb';
$user = 'avnadmin';
$pass = 'YOUR_AIVEN_PASSWORD';  // Replace with actual password

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$pass sslmode=require";

$conn = pg_connect($conn_string);

if (!$conn) {
    die("❌ Connection failed: " . pg_last_error());
}

echo "✅ Successfully connected to PostgreSQL!<br>";
echo "Database: " . $dbname . "<br>";
echo "Host: " . $host . "<br>";

// Test query
$result = pg_query($conn, "SELECT version()");
$row = pg_fetch_row($result);
echo "PostgreSQL version: " . $row[0];

pg_close($conn);
?>
