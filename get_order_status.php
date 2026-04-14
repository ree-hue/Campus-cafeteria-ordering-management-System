<?php
session_start();
include 'includes/db.php';

if(!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['order_status' => 'unknown']);
    exit();
}

$order_id = intval($_GET['order_id']);

// Use PostgreSQL prepared statement
$query = "SELECT order_status FROM orders WHERE order_id = $1 AND user_id = $2";
$result = pg_query_params($conn, $query, array($order_id, $_SESSION['user_id']));

if($row = pg_fetch_assoc($result)) {
    echo json_encode(['order_status' => $row['order_status']]);
} else {
    echo json_encode(['order_status' => 'unknown']);
}
?>