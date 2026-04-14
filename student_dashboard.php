<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'] ?? 'Student';
$user_id = $_SESSION['user_id'];

$menuResult = pg_query($conn, "SELECT * FROM menu_items WHERE availability_status = 1 ORDER BY item_id LIMIT 4");
$menu_items = [];
if ($menuResult) {
    while ($row = pg_fetch_assoc($menuResult)) {
        $menu_items[] = $row;
    }
}

$orders_query = "SELECT COUNT(*) as total_orders FROM orders WHERE user_id = $1";
$orders_result = pg_query_params($conn, $orders_query, array($user_id));
$total_orders = 0;
if ($orders_result) {
    $order_row = pg_fetch_assoc($orders_result);
    $total_orders = $order_row['total_orders'] ?? 0;
}

$pending_query = "SELECT COUNT(*) as pending_orders FROM orders WHERE user_id = $1 AND status IN ('Pending', 'Paid', 'Preparing')";
$pending_result = pg_query_params($conn, $pending_query, array($user_id));
$pending_orders = 0;
if ($pending_result) {
    $pending_row = pg_fetch_assoc($pending_result);
    $pending_orders = $pending_row['pending_orders'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Campus Cafeteria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background: linear-gradient(135deg, #e6f7f6, #f0f9ff); }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #060549, #0a0a5c);
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 25px;
            position: fixed;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            font-size: 24px;
            margin-bottom: 8px;
            color: #fff;
            text-align: center;
            font-weight: 600;
        }

        .sidebar p {
            font-size: 14px;
            margin-bottom: 30px;
            color: #d9fff2;
            text-align: center;
            opacity: 0.9;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar a i { margin-right: 12px; font-size: 18px; width: 20px; }
        .sidebar a:hover { background-color: #e2b00a; transform: translateX(5px); }

        .main-content {
            margin-left: 280px;
            padding: 30px;
            flex: 1;
            min-height: 100vh;
        }

        .welcome-section {
            background: linear-gradient(135deg, #ff9a9e, #fad0c4);
            padding: 25px;
            border-radius: 15px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .welcome-section h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .welcome-section p {
            font-size: 16px;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover { transform: translateY(-5px); }

        .stat-card h3 {
            font-size: 32px;
            color: #333;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .stat-card p {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .popular-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
            color: #ff6b6b;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .menu-card:hover { transform: translateY(-5px); }

        .menu-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #f8f9fa;
        }

        .menu-card .content {
            padding: 15px;
        }

        .menu-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .menu-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .menu-card .price {
            font-size: 18px;
            font-weight: 700;
            color: #ff6b6b;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .action-btn i {
            margin-right: 8px;
            font-size: 16px;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                flex-direction: row;
                overflow-x: auto;
                padding: 15px;
            }

            .sidebar h2, .sidebar p { display: none; }
            .sidebar a { margin-right: 10px; margin-bottom: 0; white-space: nowrap; }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .menu-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Student Dashboard</h2>
        <p>Welcome, <?php echo htmlspecialchars($name); ?>!</p>
        <a href="student_dashboard.php"><i class="fas fa-home"></i>Home</a>
        <a href="menu.php"><i class="fas fa-utensils"></i>Menu</a>
        <a href="cart.php"><i class="fas fa-shopping-cart"></i>Cart</a>
        <a href="my_orders.php"><i class="fas fa-receipt"></i>My Orders</a>
        <a href="checkout.php"><i class="fas fa-credit-card"></i>Checkout</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-section">
            <h1>Hey <?php echo htmlspecialchars($name); ?>! 👋</h1>
            <p>What are we eating today? Let's make it delicious! 😋</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_orders; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $pending_orders; ?></h3>
                <p>Active Orders</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($menu_items); ?></h3>
                <p>Menu Items</p>
            </div>
        </div>

        <div class="quick-actions">
            <a href="menu.php" class="action-btn">
                <i class="fas fa-utensils"></i> Browse Menu
            </a>
            <a href="cart.php" class="action-btn">
                <i class="fas fa-shopping-cart"></i> View Cart
            </a>
            <a href="my_orders.php" class="action-btn">
                <i class="fas fa-receipt"></i> My Orders
            </a>
            <a href="checkout.php" class="action-btn">
                <i class="fas fa-credit-card"></i> Checkout
            </a>
        </div>

        <div class="popular-section">
            <h2 class="section-title">
                <i class="fas fa-fire"></i> Popular Meals
            </h2>

            <?php if(empty($menu_items)): ?>
                <div class="error-message">
                    <i class="fas fa-info-circle"></i> No menu items available at the moment.
                </div>
            <?php else: ?>
                <div class="menu-grid">
                    <?php foreach($menu_items as $item): 
                        $imagePath = 'image/' . strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s-]/', '', $item['item_name']))) . '.jpeg';
                        if (!file_exists($imagePath)) {
                            $imagePath = 'image/Cafeteria.jpeg';
                        }
                    ?>
                        <div class="menu-card" onclick="window.location.href='menu.php'">
                            <img src="<?php echo $imagePath; ?>" 
                                 alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                 onerror="this.src='image/Cafeteria.jpeg'">
                            <div class="content">
                                <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                <p><?php echo htmlspecialchars($item['description'] ?? 'Delicious meal'); ?></p>
                                <div class="price">Ksh <?php echo number_format($item['price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
