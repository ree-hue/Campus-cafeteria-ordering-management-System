<?php
session_start();
include 'includes/db.php';

$order_id = $_GET['order_id'];

$result = $conn->query("
    SELECT oi.*, m.item_name 
    FROM order_items oi
    JOIN menu_items m ON oi.item_id = m.item_id
    WHERE oi.order_id = $order_id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Details</title>
<style>
body { font-family: Arial; background:#f7f7f7; }
.container { width:80%; margin:30px auto; }
table { width:100%; border-collapse:collapse; background:#fff; }
th, td { padding:12px; border-bottom:1px solid #ddd; text-align:center; }
th { background:#f1f1f1; }
</style>
</head>
<body>

<div class="container">
<h2>Order Details</h2>

<table>
<tr>
    <th>Item</th>
    <th>Quantity</th>
    <th>Subtotal (Ksh)</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['item_name']; ?></td>
    <td><?php echo $row['quantity']; ?></td>
    <td><?php echo number_format($row['subtotal'],2); ?></td>
</tr>
<?php endwhile; ?>

</table>

</div>

</body>
</html>