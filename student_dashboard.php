<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student'){
    header("Location: login.php");
    exit();
}
$name = $_SESSION['name'];
?>

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
        <p>What are we eating today? Let's make it delicious 😋</p>
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
        <p>✨ "Good food = good mood. Don't skip meals today!"</p>
    </div>

</div>

</body>
</html>
        
        </div>
    </div>
</body>
</html>
