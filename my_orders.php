<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM orders WHERE user_id = $1 ORDER BY order_date DESC";
$result = pg_query_params($conn, $query, array($user_id));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            background: #1e2937;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .back-btn {
            padding: 12px 24px;
            background: #334155;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #475569;
            transform: translateX(-5px);
        }

        h2 {
            color: #f97316;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .card {
            background: #1e2937;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            transition: all 0.4s ease;
            border: 1px solid #334155;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(249, 115, 22, 0.2);
            border-color: #f97316;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .order-header h3 {
            margin: 0;
            color: #f1f5f9;
            font-size: 20px;
        }

        .status {
            padding: 8px 20px;
            border-radius: 30px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pending, .paid { background: #f59e0b; }
        .preparing { background: #3b82f6; }
        .ready { background: #10b981; }
        .completed { background: #64748b; }
        .cancelled { background: #ef4444; }

        .item-list {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px dashed #475569;
        }

        .item-list p {
            margin: 8px 0;
            color: #cbd5e1;
            font-size: 15.5px;
        }

        .view-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #f97316, #fb923c);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
        }

        .view-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(249, 115, 22, 0.5);
        }

        .no-orders {
            text-align: center;
            font-size: 22px;
            color: #94a3b8;
            margin-top: 100px;
            font-weight: 500;
        }

        .no-orders::before {
            content: "📭";
            display: block;
            font-size: 60px;
            margin-bottom: 15px;
            opacity: 0.6;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>My Orders</h2>
    </div>

    <?php if ($result && pg_num_rows($result) > 0): ?>
        <?php while ($row = pg_fetch_assoc($result)): 
            $status = strtolower($row['order_status'] ?? $row['status']);
            
            $query_items = "SELECT oi.quantity, oi.subtotal, m.item_name 
                FROM order_items oi
                JOIN menu_items m ON m.item_id = oi.item_id
                WHERE oi.order_id = $1";
            $result_items = pg_query_params($conn, $query_items, array($row['order_id']));
        ?>
            <div class="card">
                <div class="order-header">
                    <div>
                        <strong>Order ID:</strong> #<?php echo $row['order_id']; ?><br>
                        <strong>Date:</strong> <?php echo date('d M Y • h:i A', strtotime($row['order_date'])); ?>
                    </div>
                    <div>
                        <strong>Total:</strong> Ksh <?php echo number_format($row['total_amount'], 2); ?><br>
                        <span class="status <?php echo htmlspecialchars($status); ?>" id="status-<?php echo $row['order_id']; ?>">
                            <?php echo ucfirst(htmlspecialchars($status)); ?>
                        </span>
                    </div>
                </div>

                <div class="item-list">
                    <strong>Items Ordered:</strong><br>
                    <?php 
                    if ($result_items) {
                        while ($item = pg_fetch_assoc($result_items)): ?>
                            <p>
                                <?php echo htmlspecialchars($item['item_name']); ?> 
                                × <?php echo $item['quantity']; ?> 
                                — Ksh <?php echo number_format($item['subtotal'], 2); ?>
                            </p>
                        <?php endwhile; 
                    } else {
                        echo '<p>Unable to load items</p>';
                    }
                    ?>
                </div>

                <a href="order_details.php?order_id=<?php echo $row['order_id']; ?>" class="view-btn">
                    View Full Details →
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-orders">You haven't placed any orders yet.</p>
    <?php endif; ?>
</div>

<script>
function refreshStatuses() {
    fetch('get_order_status.php')
        .then(res => res.json())
        .then(data => {
            data.forEach(order => {
                let statusEl = document.getElementById('status-' + order.order_id);
                if(statusEl){
                    statusEl.textContent = order.order_status;
                    statusEl.className = 'status ' + order.order_status.toLowerCase();
                }
            });
        })
        .catch(err => console.error(err));
}

setInterval(refreshStatuses, 5000);
</script>

</body>
</html>
