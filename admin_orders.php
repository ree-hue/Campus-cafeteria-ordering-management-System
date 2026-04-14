<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); 
    exit();
}

$sql = "SELECT o.*, u.name as customer_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC";

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f1f5f9;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #1e293b;
            margin-bottom: 20px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #334155;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 14px 12px;
            text-align: left;
        }
        th {
            background: #2563eb;
            color: white;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        select {
            padding: 8px;
            font-size: 14px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .status-pending { color: #f59e0b; font-weight: bold; }
        .status-preparing { color: #3b82f6; font-weight: bold; }
        .status-ready { color: #10b981; font-weight: bold; }
        .status-completed { color: #64748b; font-weight: bold; }
        .status-paid { color: #8b5cf6; font-weight: bold; }
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-size: 18px;
        }
    </style>
    <script>
        function updateStatus(orderId, selectEl) {
            const status = selectEl.value;
            const originalStatus = selectEl.getAttribute('data-original');

            if (status === originalStatus) return;

            fetch('update_order_status.php', {
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
    <div class="container">
        <h1><i class="fas fa-box"></i> Orders Management</h1>
        <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>

        <?php if (pg_num_rows($orders) > 0): ?>
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
                <?php while($order = pg_fetch_assoc($orders)): 
                    $orderStatus = $order['order_status'] ?? $order['status'];
                ?>
                <tr>
                    <td><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                    <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown'); ?></td>
                    <td><?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>
                        <select onchange="updateStatus(<?php echo $order['order_id']; ?>, this)" 
                                data-original="<?php echo htmlspecialchars($orderStatus); ?>">
                            <option value="Pending" <?php echo $orderStatus=='Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Paid" <?php echo $orderStatus=='Paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="Preparing" <?php echo $orderStatus=='Preparing' ? 'selected' : ''; ?>>Preparing</option>
                            <option value="Ready" <?php echo $orderStatus=='Ready' ? 'selected' : ''; ?>>Ready</option>
                            <option value="Completed" <?php echo $orderStatus=='Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </td>
                    <td style="text-align: left;">
                        <?php
                        $query = "SELECT oi.quantity, oi.subtotal, mi.item_name 
                                  FROM order_items oi
                                  JOIN menu_items mi ON oi.item_id = mi.item_id
                                  WHERE oi.order_id = $1";
                        $items_res = pg_query_params($conn, $query, array($order['order_id']));

                        if ($items_res) {
                            while($item = pg_fetch_assoc($items_res)) {
                                echo htmlspecialchars($item['item_name']) . 
                                     " × " . $item['quantity'] . 
                                     " (@ KSh " . number_format($item['subtotal'] / $item['quantity'], 2) . ")<br>";
                            }
                        }
                        ?>
                    </td>
                    <td><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="no-orders">
                <p>📭 No orders found.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
