<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if cart exists and has items
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: menu.php");
    exit();
}

$total = 0;
$items_summary = [];
$cart_items = [];

foreach ($_SESSION['cart'] as $item) {
    $item_price = $item['price'] ?? 0;
    $item_quantity = $item['quantity'] ?? 1;
    $item_total = $item_price * $item_quantity;
    $total += $item_total;

    $item_name = $item['name'] ?? $item['item_name'] ?? 'Unknown Item';
    $items_summary[] = $item_name . " (x" . $item_quantity . ")";

    $cart_items[] = [
        'item_id' => $item['item_id'] ?? 0,
        'name' => $item_name,
        'price' => $item_price,
        'quantity' => $item_quantity,
        'subtotal' => $item_total
    ];
}

$payment_success = false;
$payment_time = "";
$error_message = "";

if (isset($_POST['pay'])) {
    $payment_method = $_POST['payment_method'] ?? 'Unknown';
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $pin = $_POST['pin'] ?? '';

    // Validate payment method
    if (!in_array($payment_method, ['M-Pesa', 'Cash', 'Card'])) {
        $error_message = "Invalid payment method selected.";
    } elseif ($payment_method === 'M-Pesa') {
        if (empty($phone)) {
            $error_message = "Please enter your M-Pesa phone number.";
        } elseif (!preg_match('/^254[0-9]{9}$/', $phone)) {
            $error_message = "Please enter a valid Kenyan number starting with 254...";
        } elseif (empty($pin)) {
            $error_message = "Please enter your M-Pesa PIN.";
        } else {
            $payment_success = true;
            $payment_time = date("Y-m-d H:i:s");
        }
    } elseif ($payment_method === 'Cash') {
        $payment_success = true;
        $payment_time = date("Y-m-d H:i:s");
    } elseif ($payment_method === 'Card') {
        if (empty($password)) {
            $error_message = "Please enter your card password.";
        } else {
            $payment_success = true;
            $payment_time = date("Y-m-d H:i:s");
        }
    }

    if ($payment_success && empty($error_message)) {
        try {
            // Start transaction
            pg_query($conn, "BEGIN");

            // Insert order and get order_id
            $query = "INSERT INTO orders (user_id, order_date, total_amount, status)
                      VALUES ($1, NOW(), $2, 'Paid') RETURNING order_id";
            $result = pg_query_params($conn, $query, array($user_id, $total));

            if (!$result) {
                throw new Exception("Error creating order: " . pg_last_error($conn));
            }

            $row = pg_fetch_assoc($result);
            $order_id = $row['order_id'];

            // Insert order items
            $query_items = "INSERT INTO order_items (order_id, item_id, quantity, subtotal)
                            VALUES ($1, $2, $3, $4)";

            foreach ($cart_items as $item) {
                $result_items = pg_query_params($conn, $query_items,
                    array($order_id, $item['item_id'], $item['quantity'], $item['subtotal']));

                if (!$result_items) {
                    throw new Exception("Error adding order item: " . pg_last_error($conn));
                }
            }

            // Commit transaction
            pg_query($conn, "COMMIT");

            // Clear cart
            unset($_SESSION['cart']);

            $items_list = implode(", ", $items_summary);

            echo "<script>
                    alert(`✅ Payment Successful via {$payment_method}!\n\n` +
                         `Time: {$payment_time}\n` +
                         `Total: Ksh " . number_format($total, 2) . "\n` +
                         `Items: {$items_list}\n\nThank you for your order!`);
                    window.location.href='my_orders.php';
                  </script>";
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            pg_query($conn, "ROLLBACK");
            $error_message = "Payment failed: " . $e->getMessage();
            error_log("Checkout Error: " . $e->getMessage());
        }
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

        <?php if(!empty($error_message)): ?>
            <div style="background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c33;">
                <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin-bottom: 10px; color: #333;">Order Summary:</h3>
            <ul style="list-style: none; padding: 0;">
                <?php foreach($cart_items as $item): ?>
                    <li style="padding: 5px 0; border-bottom: 1px solid #eee;">
                        <?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?>
                        <span style="float: right; font-weight: bold;">Ksh <?php echo number_format($item['subtotal'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <form method="post" id="checkout-form">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required onchange="togglePaymentFields()">
                <option value="M-Pesa">M-Pesa</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
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