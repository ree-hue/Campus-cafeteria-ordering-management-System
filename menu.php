<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student'){
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

// PostgreSQL query with error handling
$result = pg_query($conn, "SELECT * FROM menu_items WHERE availability_status = 1 ORDER BY category");

if(!$result){
    $error_message = "Unable to load menu items. Please try again later.";
    error_log("Menu query failed: " . pg_last_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Campus Cafeteria Menu</title>
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
    font-size: 34px;
    font-weight: bold;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.5);
    position: relative;
}

.header::after {
    content: '🍽️';
    position: absolute;
    right: 50px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 50px;
    opacity: 0.15;
}


.menu-container {
    max-width: 1800px;
    margin: 40px auto;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 28px;
    padding: 0 15px;
}


.menu-card {
    background: #1e2937;
    width: 300px;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    transition: all 0.4s ease;
    border: 1px solid #334155;
}

.menu-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(249, 115, 22, 0.25);
    border-color: #f97316;
}

.menu-card img {
    width: 110%;
    height: 400px;
    object-fit: cover;
    border-bottom: 3px solid #f97316;
    transition: transform 0.4s ease;
}

.menu-card:hover img {
    transform: scale(1.08);
}

.menu-card-content {
    padding: 20px;
    text-align: center;
}

.menu-card h3 {
    color: #f1f5f9;
    font-size: 20px;
    margin: 10px 0 8px;
    font-weight: 600;
}

.menu-card p {
    color: #94a3b8;
    font-size: 18.5px;
    margin-bottom: 5px;
    min-height: 42px;
}

.menu-card .price {
    font-size: 22px;
    font-weight: bold;
    color: #f97316;
    margin: 10px 0;
    margin-left: 20px;
}


.menu-card input[type=number] {
    width: 100px;
    padding: 10px;
    text-align: center;
    background: #334155;
    border: 2px solid #475569;
    border-radius: 11px;
    color: #e2e8f0;
    font-size: 19px;
    margin-bottom: 15px;
    margin-left: 20px;

}

.menu-card input[type=number]:focus {
    border-color: #f97316;
    outline: none;
}

.menu-card button {
    background: linear-gradient(135deg, #f97316, #fb923c);
    color: white;
    border: none;
    padding: 14px 28px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 700;
    width: 100%;
    transition: all 0.3s ease;
    margin-bottom: 5px;
}

.menu-card button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);
}


.back-btn {
    display: inline-block;
    margin: 25px 0 0 30px;
    background: #334155;
    color: white;
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s;
}

.back-btn:hover {
    background: #475569;
    transform: translateX(-6px);
}


@media (max-width: 600px) {
    .menu-card {
        width: 92%;
    }
    .header {
        font-size: 26px;
        padding: 25px 15px;
    }
}
    </style>
</head>
<body>
    <div class="header">Campus Cafeteria Menu</div>
    <a class="back-btn" href="student_dashboard.php">← Back to Dashboard</a>

    <div class="menu-container">
        <?php if(isset($error_message)): ?>
            <div style="background: #fee; color: #c33; padding: 20px; border-radius: 8px; text-align: center; margin: 20px auto; max-width: 500px; border-left: 4px solid #c33;">
                <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php elseif($result && pg_num_rows($result) > 0): ?>
            <?php while($item = pg_fetch_assoc($result)): ?>
                <div class="menu-card">
                    <img src="image/Cafeteria.jpeg"
                         alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                         style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px;">
                    <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                    <p><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                    <p class="price">Ksh <?php echo number_format($item['price'],2); ?></p>
                    <input type="number" value="1" min="1" id="qty_<?php echo $item['item_id']; ?>">
                    <button onclick="addToCart(<?php echo $item['item_id']; ?>)">Add to Cart</button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#ccc; width:100%; font-size: 18px; margin-top: 50px;">
                <i class="fas fa-utensils" style="display: block; font-size: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                No menu items available at the moment.
            </p>
        <?php endif; ?>
    </div>

    <script>
        function addToCart(itemId) {
            const qty = document.getElementById('qty_' + itemId).value;
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_id: itemId, quantity: qty })
            })
            .then(response => response.text())
            .then(msg => alert(msg))
            .catch(err => console.error(err));
        }
    </script>
</body>
</html>