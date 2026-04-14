<?php
session_start();
include 'includes/db.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); 
    exit();
}


$sql = "SELECT o.*, 
               CONCAT(c.first_name, ' ', c.last_name) AS customer_name 
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.customer_id 
        ORDER BY o.created_at DESC";

// Use PostgreSQL query
$orders = pg_query($conn, $sql);

if (!$orders) {
    die("Error fetching orders: " . pg_last_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        select {
            padding: 8px;
            font-size: 14px;
        }
        button {
            padding: 8px 12px;
            cursor: pointer;
        }
        .status-pending { color: orange; font-weight: bold; }
        .status-processing { color: blue; font-weight: bold; }
        .status-completed { color: green; font-weight: bold; }
        .status-cancelled { color: red; font-weight: bold; }
    </style>
    <script>
        function updateStatus(orderId, selectEl) {
            const status = selectEl.value;
            const originalStatus = selectEl.getAttribute('data-original');

            if (status === originalStatus) return;

            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `order_id=${orderId}&status=${encodeURIComponent(status)}`
            })
            .then(res => res.text())
            .then(data => {
                alert(data);
                
                selectEl.setAttribute('data-original', status);
            })
            .catch(err => {
                alert("Error updating status: " + err);
                
                selectEl.value = originalStatus;
            });
        }
    </script>
</head>
<body>
    <h1>Orders Management</h1>
    <p><a href="admin_dashboard.php">← Back to Dashboard</a></p>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total (KSh)</th>
                <th>Status</th>
                <th>Items</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($order = pg_fetch_assoc($orders)): ?>
            <tr>
                <td><strong>#<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                <td><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></td>
                <td><?= number_format($order['total'], 2) ?></td>
                <td>
                    <select onchange="updateStatus(<?= $order['order_id'] ?>, this)" 
                            data-original="<?= htmlspecialchars($order['status']) ?>">
                        <option value="Pending"    <?= $order['status']=='Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Processing" <?= $order['status']=='Processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="Completed"  <?= $order['status']=='Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled"  <?= $order['status']=='Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </td>
                <td style="text-align: left;">
                    <?php
                    // Use PostgreSQL prepared statement
                    $query = "SELECT product_name, quantity, price 
                              FROM order_items 
                              WHERE order_id = $1";
                    $items_res = pg_query_params($conn, $query, array((int)$order['order_id']));

                    while($item = pg_fetch_assoc($items_res)) {
                        echo htmlspecialchars($item['product_name']) . 
                             " × " . $item['quantity'] . 
                             " (@ KSh " . number_format($item['price'], 2) . ")<br>";
                    }
                    ?>
                </td>
                <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (pg_num_rows($orders) === 0): ?>
        <p>No orders found.</p>
    <?php endif; ?>
</body>
</html>