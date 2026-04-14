<?php
session_start();
include 'includes/db.php';

if(!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['order_status' => 'unknown']);
    exit();
}

$order_id = intval($_GET['order_id']);


$stmt = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if($row = $result->fetch_assoc()) {
    echo json_encode(['order_status' => $row['order_status']]);
} else {
    echo json_encode(['order_status' => 'unknown']);
}
?>