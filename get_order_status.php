<?php
session_start();
header('Content-Type: application/json');
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT order_id, order_status FROM orders WHERE user_id = $1 ORDER BY order_date DESC";
$result = pg_query_params($conn, $query, array($user_id));

$orders = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $orders[] = [
            'order_id' => $row['order_id'],
            'order_status' => $row['order_status']
        ];
    }
}

echo json_encode($orders);
?>
