<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();


include '../includes/db.php';


if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? 'Check db.php'));
}


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied!");
}


$from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$to_date   = $_GET['to_date'] ?? date('Y-m-d');


if ($from_date > $to_date) {
    list($from_date, $to_date) = [$to_date, $from_date];
}


$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount),0) as total_revenue,
        COALESCE(AVG(total_amount),0) as avg_order_value,
        COUNT(CASE WHEN order_status = 'Completed' THEN 1 END) as completed_orders
    FROM orders
    WHERE DATE(order_date) BETWEEN ? AND ?
");

if (!$stmt) die("Summary query error: " . $conn->error);

$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();


$stmt2 = $conn->prepare("
    SELECT order_id, user_id, order_date, total_amount, order_status
    FROM orders
    WHERE DATE(order_date) BETWEEN ? AND ?
    ORDER BY order_date DESC
");

if (!$stmt2) die("Details query error: " . $conn->error);

$stmt2->bind_param("ss", $from_date, $to_date);
$stmt2->execute();
$result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Report</title>
<style>
body { font-family: Arial; margin: 20px; color: #000; }
h1,h2 { text-align:center; }
table { width:100%; border-collapse:collapse; margin:20px 0; }
th, td { border:1px solid #000; padding:10px; }
th { background:#f0f0f0; }
.summary { display:flex; justify-content:space-around; margin:30px 0; font-size:16px; }
.footer { text-align:center; margin-top:40px; font-size:12px; }
@media print { body { margin:10px; } }
</style>
</head>
<body>

<h1>📊 Sales Report</h1>
<p style="text-align:center;">
    <?= date('d M Y', strtotime($from_date)) ?> — <?= date('d M Y', strtotime($to_date)) ?>
</p>

<div class="summary">
    <div><strong>Orders:</strong> <?= number_format($summary['total_orders']) ?></div>
    <div><strong>Revenue:</strong> Ksh <?= number_format($summary['total_revenue'], 2) ?></div>
    <div><strong>Average:</strong> Ksh <?= number_format($summary['avg_order_value'], 2) ?></div>
    <div><strong>Completed:</strong> <?= number_format($summary['completed_orders']) ?></div>
</div>

<h2>Order Details</h2>

<?php if ($result->num_rows > 0): ?>
<table>
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Date</th>
    <th>Amount</th>
    <th>Status</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td>#<?= $row['order_id'] ?></td>
    <td><?= $row['user_id'] ?></td>
    <td><?= date('d M Y h:i A', strtotime($row['order_date'])) ?></td>
    <td><?= number_format($row['total_amount'], 2) ?></td>
    <td><?= $row['order_status'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p style="text-align:center;">No orders found.</p>
<?php endif; ?>

<div class="footer">
    Generated on <?= date('d M Y • h:i A') ?>
</div>

<script>window.print();</script>
</body>
</html>