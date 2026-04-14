<?php
session_start();


include 'includes/db.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied!");
}


$from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$to_date   = $_GET['to_date'] ?? date('Y-m-d');


if ($from_date > $to_date) {
    list($from_date, $to_date) = [$to_date, $from_date];
}


// Use PostgreSQL prepared statement
$query1 = "
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount),0) as total_revenue,
        COALESCE(AVG(total_amount),0) as avg_order_value,
        COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_orders
    FROM orders 
    WHERE DATE(order_date) BETWEEN $1 AND $2
";

$summary_result = pg_query_params($conn, $query1, array($from_date, $to_date));

if (!$summary_result) {
    die("Query error: " . pg_last_error($conn));
}

$summary = pg_fetch_assoc($summary_result);

// PostgreSQL prepared statement for orders list
$query2 = "
    SELECT order_id, user_id, order_date, total_amount, status
    FROM orders 
    WHERE DATE(order_date) BETWEEN $1 AND $2
    ORDER BY order_date DESC
";

$result = pg_query_params($conn, $query2, array($from_date, $to_date));

if (!$result) {
    die("Query error: " . pg_last_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Report</title>

<style>
body {
    font-family: Arial;
    margin: 20px;
    color: #000;
}

h1, h2 {
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

th, td {
    border: 1px solid #000;
    padding: 10px;
}

th {
    background: #f0f0f0;
}

.summary {
    display: flex;
    justify-content: space-around;
    margin: 30px 0;
    font-size: 16px;
}

.footer {
    text-align: center;
    margin-top: 40px;
    font-size: 12px;
}

@media print {
    body { margin: 10px; }
}
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

<?php if (pg_num_rows($result) > 0): ?>

<table>
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Date</th>
    <th>Amount</th>
    <th>Status</th>
</tr>

<?php while ($row = pg_fetch_assoc($result)): ?>
<tr>
    <td>#<?= $row['order_id'] ?></td>
    <td><?= $row['user_id'] ?></td>
    <td><?= date('d M Y h:i A', strtotime($row['order_date'])) ?></td>
    <td><?= number_format($row['total_amount'], 2) ?></td>
    <td><?= $row['status'] ?></td>
</tr>
<?php endwhile; ?>

</table>

<?php else: ?>
<p style="text-align:center;">No orders found.</p>
<?php endif; ?>

<div class="footer">
    Generated on <?= date('d M Y • h:i A') ?>
</div>

<script>
window.print();
</script>

</body>
</html>   