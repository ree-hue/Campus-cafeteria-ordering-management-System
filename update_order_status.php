<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'includes/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo "Access denied!";
    exit();
}


$order_id = intval($_POST['order_id'] ?? 0);
$status   = $_POST['status'] ?? '';

$valid_status = ['Pending','Preparing','Ready','Completed','Cancelled'];
if ($order_id <= 0 || !in_array($status, $valid_status)) {
    echo "Invalid input!";
    exit();
}

// Use PostgreSQL prepared statement
$query = "UPDATE orders SET status=$1 WHERE order_id=$2";
$result = pg_query_params($conn, $query, array($status, $order_id));

if ($result) {
    echo "Order #$order_id status updated to $status!";
} else {
    echo "Failed to update status: " . pg_last_error($conn);
}