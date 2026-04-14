<?php
session_start();
include 'includes/db.php';

// Check if user is logged in and is student
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Student'){
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'] ?? 'Student';
$user_id = $_SESSION['user_id'];

// Get user's order statistics
$orders_query = "SELECT COUNT(*) as total_orders FROM orders WHERE user_id = $1";
$orders_result = pg_query_params($conn, $orders_query, array($user_id));
$total_orders = 0;
if ($orders_result) {
    $order_row = pg_fetch_assoc($orders_result);
    $total_orders = $order_row['total_orders'] ?? 0;
}

// Get pending orders
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

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_orders; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $pending_orders; ?></h3>
                <p>Active Orders</p>
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
    </div>
</body>
</html>

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
                <h3><?php echo $order_count; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $pending_count; ?></h3>
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
                    <?php foreach($menu_items as $item): ?>
                        <div class="menu-card" onclick="window.location.href='menu.php'">
                            <img src="image/menu_<?php echo htmlspecialchars($item['item_id']); ?>.jpg"
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { display: flex; min-height: 100vh; background: #e6f7f6; }

    
        .sidebar {
            width: 250px;
            background: #060549; /* greenish as in image */
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 20px 40px;
            position: fixed;
            height: 200%;
            margin-left: -40px; 
            margin-top: -20px; 
        }
        .sidebar h2 { font-size: 30px; margin-bottom: 10px; color: #fff; text-align: center; }
        .sidebar p { font-size: 16px; margin-bottom: 30px; color: #d9fff2; text-align: center; }
        .sidebar a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #fff;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .sidebar a i { margin-right: 12px; font-size: 18px; }
        .sidebar a:hover { background-color: #e2b00a; }

        
        .main-content {
            margin-left: 270px;
            padding: 30px 50px;
            flex: 1;
        }

        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-bar {
            display: flex;
            gap: 20px;
        }
        .search-bar input {
            padding: 10px 15px;
            border-radius: 30px;
            border: 1px solid #ccc;
            width: 300px;
            outline: none;
        }
        .search-bar button {
            background: #ffb400;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        .search-bar button:hover { background: #e69c00; }

        .profile-icon {
            font-size: 24px;
            background: #ffb400;
            color: #fff;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
        }

        
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #333; }
        .recommendations {
            background: #ffb400;
            padding: 15px 20px;
            border-radius: 12px;
            display: flex;
            overflow-x: auto;
            gap: 15px;
            margin-bottom: 30px;
        }
        .recommendations::-webkit-scrollbar { height: 8px; }
        .recommendations::-webkit-scrollbar-thumb { background: #ffb400; border-radius: 4px; }
        .recommendation-card {
            background: #fff;
            padding: 10px;
            border-radius: 10px;
            min-width: 150px;
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .recommendation-card:hover { transform: translateY(-5px); }
        .recommendation-card img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .recommendation-card p { font-size: 14px; font-weight: 500; color: #333; text-align: center; }

        
        .menu-categories {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        .category-card {
            background: #e0f7f6;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            flex: 1;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .category-card:hover { transform: translateY(-5px); }
        .category-card i { font-size: 28px; margin-bottom: 8px; display: block; }
        .category-card p { font-weight: 500; color: #333; }
        .category-breakfast { background: #0e8de2; }
        .category-lunch { background: #0e8de2; }
        .category-snacks { background: #0e8de2; }
        .category-drinks { background: #0e8de2; }

        
        .choose-order-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #333; }
        .choose-order {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }
        .food-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            cursor: pointer;
        }
        .food-card:hover { transform: translateY(-5px); }
        .food-card img { width: 100%; height: 150px; object-fit: cover; }
        .food-card p { padding: 10px; font-size: 14px; color: #333; text-align: center; }

        
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; flex-direction: row; overflow-x: auto; padding: 15px; position: relative; }
            .sidebar h2, .sidebar p { display: none; }
            .sidebar a { margin-right: 10px; margin-bottom: 0; white-space: nowrap; }
            .main-content { margin-left: 0; padding: 20px; }
            .menu-categories { flex-direction: column; gap: 10px; }
            .recommendation-card { min-width: 130px; }
            .choose-order { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
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
        



</style>     
        <div class="menu-categories">
            <div class="category-card category-breakfast"><i class="fas fa-bread-slice"></i><p>Breakfast</p></div>
            <div class="category-card category-lunch"><i class="fas fa-hamburger"></i><p>Lunch</p></div>
            <div class="category-card category-snacks"><i class="fas fa-cookie-bite"></i><p>Snacks</p></div>
            <div class="category-card category-drinks"><i class="fas fa-coffee"></i><p>Drinks</p></div>
        </div>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard</title>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #fdfbfb, #ebedee);
    margin: 0;
    padding: 20px;
}

.dashboard {
    max-width: 1000px;
    margin: auto;
}


.welcome-card {
    background: linear-gradient(135deg, #ff9a9e, #fad0c4);
    padding: 20px;
    border-radius: 15px;
    color: white;
    margin-bottom: 20px;
}


.stats-grid {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    flex: 1;
    background: white;
    padding: 15px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 10px rgba(0,0,0,0.05);
    transition: 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}


.orders-preview {
    background: white;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.order-pill {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 20px;
    color: white;
    margin-right: 10px;
    font-size: 13px;
}

.pending { background: #f59e0b; }
.preparing { background: #3b82f6; }
.ready { background: #10b981; }


.menu-categories {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.category-card {
    flex: 1;
    padding: 20px;
    border-radius: 12px;
    color: white;
    text-align: center;
    cursor: pointer;
    transition: 0.3s;
}

.category-card:hover {
    transform: scale(1.05);
}

.category-breakfast { background: #ffb347; }
.category-lunch { background: #ff6961; }
.category-snacks { background: #77dd77; }
.category-drinks { background: #84b6f4; }


.popular {
    margin-bottom: 20px;
}

.popular-grid {
    display: flex;
    gap: 10px;
}

.food-card {
    flex: 1;
    background: white;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    transition: 0.3s;
}

.food-card:hover {
    transform: translateY(-5px);
}


.quick-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.quick-actions a {
    flex: 1;
    text-decoration: none;
    padding: 15px;
    background: #2563eb;
    color: white;
    border-radius: 10px;
    text-align: center;
    transition: 0.3s;
}

.quick-actions a:hover {
    background: #1e40af;
}


.daily-message {
    text-align: center;
    font-style: italic;
    color: #555;
}
</style>
</head>

<body>

<div class="dashboard">

    
    <div class="welcome-card">
        <h2>Hey <?= $_SESSION['name'] ?? 'Student' ?> 👋</h2>
        <p>What are we eating today? Let’s make it delicious 😋</p>
    </div>

    
        <div class="stat-card">
            <h3>5</h3>
            <p>Orders Today</p>
        </div>
        <div class="stat-card">
            <h3>2</h3>
            <p>Pending Orders</p>
        </div>
    </div>

    
    <div class="orders-preview">
        <h3>Your Orders</h3>
        <div class="order-pill pending">Pending</div>
        <div class="order-pill preparing">Preparing</div>
        <div class="order-pill ready">Ready</div>
    </div>

    
    
    <div class="popular">
        <h3>🔥 Popular Meals</h3>
        <div class="popular-grid">
            <div class="food-card">🍗 Chicken pilau</div>
            <div class="food-card">🥘 Meatball spaghetti</div>
            <div class="food-card">🥞 Pancakes</div>
            <div class="food-card">☕ Coffee</div>
        </div>
    </div>

    


    <div class="daily-message">
        <p>✨ “Good food = good mood. Don’t skip meals today!”</p>
    </div>

</div>

</body>
</html>
        
        </div>
    </div>
</body>
</html>