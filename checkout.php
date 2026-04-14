<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];




$total = 0;
$items_summary = [];

foreach ($_SESSION['cart'] as $item) {
    $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
    
    
    $item_name = $item['name'] ?? $item['item_name'] ?? 'Unknown Item';
    $items_summary[] = $item_name . " (x" . ($item['quantity'] ?? 1) . ")";
}

$payment_success = false;
$payment_time = "";

if (isset($_POST['pay'])) {
    $payment_method = $_POST['payment_method'] ?? 'Unknown';
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $pin = $_POST['pin'] ?? '';

    if ($payment_method === 'M-Pesa') {
        if (empty($phone) || empty($pin)) {
            echo "<script>alert('Please fill in Phone Number and PIN for M-Pesa.');</script>";
        } elseif (!preg_match('/^254[0-9]{9}$/', $phone)) {
            echo "<script>alert('Please enter a valid Kenyan number starting with 254...');</script>";
        } else {
            $payment_success = true;
            $payment_time = date("Y-m-d H:i:s");
        }
    } else {
        
        $payment_success = true;
        $payment_time = date("Y-m-d H:i:s");
    }

    if ($payment_success) {
        
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total_amount, Status) VALUES (?, NOW(), ?, 'Paid')");
        $stmt->bind_param("id", $user_id, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        
        $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
        
        foreach ($_SESSION['cart'] as $item) {
            $item_id = $item['item_id'] ?? 0;
            $qty = $item['quantity'] ?? 1;
            $subtotal = ($item['price'] ?? 0) * $qty;

            $stmt_items->bind_param("iiid", $order_id, $item_id, $qty, $subtotal);
            $stmt_items->execute();
        }
        $stmt_items->close();

        unset($_SESSION['cart']);

        $items_list = implode(", ", $items_summary);

        echo "<script>
                alert(`✅ Payment Successful via {$payment_method}!\n\n` +
                     `Time: {$payment_time}\n` +
                     `Total: Ksh " . number_format($total, 2) . "\n` +
                     `Items: {$items_list}\n\nThank you!`);
                window.location.href='my_orders.php';
              </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <style>
        body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e2937);
    color: #e2e8f0;
    margin: 0;
    padding: 40px 20px;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.checkout-container {
    background: #1e2937;
    padding: 40px 35px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
    width: 100%;
    max-width: 460px;
    text-align: center;
    border: 1px solid #334155;
}

h2 {
    color: #f97316;
    margin-bottom: 10px;
    font-size: 28px;
    font-weight: 700;
}

.checkout-container p {
    font-size: 20px;
    font-weight: bold;
    color: #f1f5f9;
    margin-bottom: 30px;
}


label {
    display: block;
    margin: 18px 0 8px;
    text-align: left;
    font-weight: 500;
    color: #cbd5e1;
}

input, select {
    width: 100%;
    padding: 14px 16px;
    border-radius: 10px;
    border: 2px solid #475569;
    background: #334155;
    color: #e2e8f0;
    font-size: 16px;
    transition: all 0.3s;
}

input:focus, select:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.2);
}

.pay-btn, .back-btn {
    margin-top: 25px;
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pay-btn {
    background: linear-gradient(135deg, #f97316, #fb923c);
    color: white;
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4);
}

.pay-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(249, 115, 22, 0.5);
}

.back-btn {
    background: #475569;
    color: white;
    text-decoration: none;
    display: inline-block;
}

.back-btn:hover {
    background: #64748b;
    transform: translateY(-2px);
}


#mpesa-fields {
    display: none;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px dashed #475569;
}


.checkout-container::before {
    content: '💳';
    font-size: 60px;
    opacity: 0.08;
    position: absolute;
    top: 30px;
    right: 30px;
}


@media (max-width: 500px) {
    .checkout-container {
        padding: 30px 20px;
        margin: 10px;
    }
    h2 {
        font-size: 24px;
    }
}
    </style>
</head>
<body>
    <div class="checkout-container">
        <h2>Checkout</h2>
        <p><strong>Total Amount: Ksh <?php echo number_format($total, 2); ?></strong></p>

        <form method="post" id="checkout-form">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required onchange="togglePaymentFields()">
                <option value="M-Pesa">M-Pesa</option>
        
            </select>

            <div id="mpesa-fields">
                <label for="phone">Phone Number (254...)</label>
                <input type="text" name="phone" id="phone" placeholder="254712345678" maxlength="12">

                

                <label for="pin">M-Pesa PIN</label>
                <input type="password" name="pin" id="pin" maxlength="4">
            </div>

            <button type="submit" name="pay" class="pay-btn">Pay Now</button>
        </form>

        <a class="back-btn" href="cart.php">← Back to Cart</a>
    </div>

    <script>
        function togglePaymentFields() {
            const method = document.getElementById('payment_method').value;
            document.getElementById('mpesa-fields').style.display = (method === 'M-Pesa') ? 'block' : 'none';
        }
        window.onload = togglePaymentFields;
    </script>
</body>
</html>