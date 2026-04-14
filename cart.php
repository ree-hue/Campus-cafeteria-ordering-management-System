<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student'){
    header("Location: login.php");
    exit();
}


if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];


foreach($_SESSION['cart'] as $id => $item){
    if(!isset($item['price'])) $_SESSION['cart'][$id]['price'] = 0;
    if(!isset($item['item_name'])) $_SESSION['cart'][$id]['item_name'] = 'Item';
    if(!isset($item['quantity'])) $_SESSION['cart'][$id]['quantity'] = 1;
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $item_id = $data['item_id'] ?? 0;

    if($action === 'update'){
        $qty = max(1, intval($data['quantity']));
        if(isset($_SESSION['cart'][$item_id])){
            $_SESSION['cart'][$item_id]['quantity'] = $qty;
        }
        echo "Quantity updated!";
        exit;
    } elseif($action === 'remove'){
        if(isset($_SESSION['cart'][$item_id])){
            unset($_SESSION['cart'][$item_id]);
        }
        echo "Item removed!";
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart - Campus Cafeteria</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #0f172a;
    color: #e2e8f0;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

.header {
    background: linear-gradient(135deg, #1e3a8a, #1e40af);
    color: white;
    padding: 35px 20px;
    text-align: center;
    font-size: 32px;
    font-weight: bold;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.4);
    position: relative;
}

.header::after {
    content: '🛒';
    position: absolute;
    right: 40px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 40px;
    opacity: 0.2;
}


.cart-container {
    max-width: 1000px;
    margin: 40px auto;
    background: #1e2937;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
}


table {
    width: 100%;
    border-collapse: collapse;
    background: #1e2937;
    border-radius: 12px;
    overflow: hidden;
}

th, td {
    padding: 18px 12px;
    text-align: center;
    border-bottom: 1px solid #334155;
}

th {
    background: #1e40af;
    color: white;
    font-weight: 600;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

td {
    color: #e2e8f0;
    font-size: 15.5px;
}

tr:hover {
    background: #334155;
    transition: all 0.3s ease;
}


input[type=number] {
    width: 70px;
    padding: 10px;
    text-align: center;
    background: #334155;
    border: 2px solid #475569;
    border-radius: 8px;
    color: #f1f5f9;
    font-size: 16px;
}

input[type=number]:focus {
    border-color: #f97316;
    outline: none;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.3);
}


button {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

button:hover {
    transform: translateY(-2px);
}


.remove-btn {
    background: #ef4444;
    color: white;
}

.remove-btn:hover {
    background: #dc2626;
}


.update-btn {
    background: #3b82f6;
    color: white;
}

.update-btn:hover {
    background: #2563eb;
}


.total {
    font-size: 22px;
    font-weight: bold;
    color: #f97316;
    text-align: right;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 2px solid #475569;
}


.cart-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.back-btn {
    background: #475569;
    color: white;
    padding: 14px 24px;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s;
}

.back-btn:hover {
    background: #64748b;
    transform: translateX(-5px);
}

.checkout-btn {
    background: linear-gradient(135deg, #f97316, #fb923c);
    color: white;
    padding: 14px 32px;
    text-decoration: none;
    border-radius: 10px;
    font-size: 18px;
    font-weight: 700;
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4);
    transition: all 0.3s;
}

.checkout-btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(249, 115, 22, 0.5);
}


@media (max-width: 768px) {
    .header { font-size: 26px; padding: 25px 15px; }
    th, td { padding: 14px 8px; font-size: 14px; }
    .cart-container { margin: 20px 10px; padding: 20px; }
}
        
    </style>
</head>
<body>
    <div class="header">Your Cart</div>
    <a class="back-btn" href="menu.php">← Back to Menu</a>

    <div class="cart-container">
        <?php if(!empty($_SESSION['cart'])): ?>
        <table>
            <tr>
                <th>Item</th>
                <th>Price (Ksh)</th>
                <th>Quantity</th>
                <th>Subtotal (Ksh)</th>
                <th>Action</th>
            </tr>
            <?php 
            $total = 0;
            foreach($_SESSION['cart'] as $item_id => $item): 
                $price = floatval($item['price']);
                $quantity = intval($item['quantity']);
                $subtotal = $price * $quantity;
                $total += $subtotal;
            ?>
            <tr id="row_<?php echo $item_id; ?>">
                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                <td><?php echo number_format($price,2); ?></td>
                <td><input type="number" value="<?php echo $quantity; ?>" min="1" onchange="updateQty(<?php echo $item_id; ?>, this.value)"></td>
                <td id="subtotal_<?php echo $item_id; ?>"><?php echo number_format($subtotal,2); ?></td>
                <td><button onclick="removeItem(<?php echo $item_id; ?>)">Remove</button></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" class="total">Total</td>
                <td colspan="2" class="total" id="total"><?php echo number_format($total,2); ?></td>
            </tr>
        </table>
        <br>
        <button onclick="checkout()">Proceed to Checkout</button>
        <?php else: ?>
            <p style="text-align:center; color:#555;">Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <script>
        function updateQty(itemId, qty){
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action:'update', item_id:itemId, quantity:qty })
            })
            .then(() => location.reload());
        }

        function removeItem(itemId){
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action:'remove', item_id:itemId })
            })
            .then(() => location.reload());
        }

        function checkout(){
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>  