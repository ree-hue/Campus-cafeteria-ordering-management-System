<?php
session_start();
include 'includes/db.php';

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin'){
    header("Location: login.php");
    exit();
}


$orders = pg_query($conn, "SELECT * FROM orders ORDER BY order_date DESC LIMIT 10");

// Fetch aggregates - PostgreSQL style
$totalResult = pg_query($conn, "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders");
$totalRow = pg_fetch_assoc($totalResult);
$totalSales = $totalRow['total'] ?? 0;

$countResult = pg_query($conn, "SELECT COUNT(*) AS count FROM orders");
$countRow = pg_fetch_assoc($countResult);
$totalOrders = $countRow['count'] ?? 0;

$completeResult = pg_query($conn, "SELECT COUNT(*) AS count FROM orders WHERE status='Completed'");
$completeRow = pg_fetch_assoc($completeResult);
$completedOrders = $completeRow['count'] ?? 0;


if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['add_item'])){
        $name = $_POST['item_name'];
        $price = $_POST['price'];
        $desc = $_POST['description'];
        // Use PostgreSQL prepared statement
        $query = "INSERT INTO menu_items (item_name, price, description) VALUES ($1, $2, $3)";
        pg_query_params($conn, $query, array($name, $price, $desc));
    }

    if(isset($_POST['update_item'])){
        $id = $_POST['item_id'];
        $name = $_POST['item_name'];
        $price = $_POST['price'];
        $desc = $_POST['description'];
        // Use PostgreSQL prepared statement
        $query = "UPDATE menu_items SET item_name=$1, price=$2, description=$3 WHERE item_id=$4";
        pg_query_params($conn, $query, array($name, $price, $desc, $id));
    }

    if(isset($_POST['delete_item'])){
        $id = $_POST['item_id'];
        // Use PostgreSQL prepared statement
        $query = "DELETE FROM menu_items WHERE item_id=$1";
        pg_query_params($conn, $query, array($id));
    }

    // Redirect safely
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<style>
body {
    margin:0;
    font-family:'Segoe UI';
    display:flex;
    background:#f1f5f9;
}


.sidebar {
    width:240px;
    height:200vh;
    background:#0f172a;
    color:white;
    padding:20px;
}

.sidebar h2 {
    margin-bottom:30px;
}

.sidebar a {
    display:block;
    padding:12px;
    margin-bottom:10px;
    color:#cbd5e1;
    text-decoration:none;
    border-radius:8px;
    transition:0.3s;
    cursor:pointer;
}

.sidebar a:hover {
    background:#2563eb;
    color:white;
}


.main {
    flex:1;
    padding:20px;
    overflow-x:auto;
}


.card {
    background:white;
    padding:15px;
    border-radius:10px;
    margin-bottom:15px;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
}


.report-container {
    display:flex;
    gap:20px;
    flex-wrap:wrap;
}

.report-box {
    flex:1;
    min-width:200px;
    background:#2563eb;
    color:white;
    padding:20px;
    border-radius:10px;
}


.form-container, .table-container {
    margin-top:20px;
    background:white;
    padding:15px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

.table {
    width:100%;
    border-collapse:collapse;
}

.table th, .table td {
    border:1px solid #ddd;
    padding:8px;
    text-align:left;
}

.table th {
    background:#2563eb;
    color:white;
}

input[type=text], input[type=number], select {
    padding:5px;
    border-radius:5px;
    border:1px solid #ccc;
}

button {
    background:#2563eb;
    color:white;
    border:none;
    padding:6px 10px;
    border-radius:6px;
    cursor:pointer;
    margin-top:5px;
}

button:hover {
    background:#1e40af;
}


.section {
    display:none;
}

.section.active {
    display:block;
}
</style>

<script>
function showSection(sectionId){
    document.querySelectorAll('.section').forEach(sec=>{
        sec.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');
}
</script>

</head>
<body>


<div class="sidebar">
    <h2>Admin Panel</h2>
    <a onclick="showSection('dashboard')">🏠 Dashboard</a>
    <a onclick="showSection('orders')">📦 Orders</a>
    <a onclick="showSection('menu-management')">🍔 Menu Management</a>
    <a href="manage_admins.php">👥 Manage Admins</a>
    <a href="reports.php">📊 Reports</a>
    <a href="logout.php">🚪 Logout</a>
</div>


<div class="main">


<div id="dashboard" class="section active">

<style>

.dashboard-header h1 {
    margin: 0;
    color: #0f172a;
}
.dashboard-header p {
    color: #64748b;
    margin-bottom: 20px;
}


.alert-box {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
}
.alert-box.success {
    background: #dcfce7;
    color: #166534;
}


.dashboard-actions {
    margin-bottom: 20px;
}
.dashboard-actions button {
    margin-right: 10px;
    padding: 10px 15px;
    border: none;
    background: #2563eb;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.3s;
}
.dashboard-actions button:hover {
    background: #1e40af;
    transform: scale(1.05);
}


.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}


.card {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 5px 12px rgba(0,0,0,0.08);
}
.card h3 {
    margin-bottom: 10px;
    color: #2563eb;
}
.card p {
    margin: 5px 0;
    color: #334155;
}
</style>

<div class="dashboard-header">
    <h1>Admin Control Center ⚡</h1>
    <p>Monitor activity, respond fast, and stay in control.</p>
</div>


<div class="alerts">
<?php
// Use PostgreSQL query
$pendingResult = pg_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='Pending'");
$pendingRow = pg_fetch_assoc($pendingResult);
$pending = $pendingRow['c'] ?? 0;

if($pending > 0){
    echo "<div class='alert-box'>
            ⚠️ You have <strong>$pending pending orders</strong> that need attention!
          </div>";
} else {
    echo "<div class='alert-box success'>
            ✅ All orders are handled. Great job!
          </div>";
}
?>
</div>




<div class="dashboard-grid">

    
    <div class="card">
        <h3>Recent Orders</h3>
        <?php
        $recent = pg_query($conn, "SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
        if($recent && pg_num_rows($recent) > 0){
            while($r = pg_fetch_assoc($recent)){
                echo "<p>Order #{$r['order_id']} - Ksh ".number_format($r['total_amount'],2)." ({$r['order_status']})</p>";
            }
        } else {
            echo "<p>No recent orders</p>";
        }
        ?>
    </div>

    
    <div class="card">
        <h3>System Status</h3>
        <p>🟢 Database Connected</p>
        <p>🟢 Orders System Running</p>
        <p>🟢 Menu Active</p>
    </div>

    
    <div class="card">
        <h3>Today's Activity</h3>
        <?php
        $today = date('Y-m-d');
        // Use PostgreSQL prepared statement to prevent SQL injection
        $query = "SELECT COUNT(*) as c FROM orders WHERE DATE(order_date) = $1";
        $todayResult = pg_query_params($conn, $query, array($today));
        $todayRow = pg_fetch_assoc($todayResult);
        $todayOrders = $todayRow['c'] ?? 0;

        echo "<p>📦 Orders today: $todayOrders</p>";
        ?>
    </div>

    
    <div class="card">
        <h3>Quick Info</h3>
        <p>💡 Tip: Update menu regularly to attract more orders</p>
        <p>⚡ Keep track of pending orders for better service</p>
    </div>

</div>

</div>


<div id="orders" class="section"> 
    <h2>Orders</h2>
    <?php if($orders && pg_num_rows($orders) > 0): ?>
        <?php while($row = pg_fetch_assoc($orders)): ?>
            <div class="card">
                <p><strong>Order ID:</strong> <?= $row['order_id']; ?></p>
                <p><strong>User ID:</strong> <?= $row['user_id']; ?></p>
                <p><strong>Total:</strong> Ksh <?= number_format($row['total_amount'],2); ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($row['order_status'] ?? $row['status']); ?></p>
                <form method="POST" action="update_order_status.php">
                    <input type="hidden" name="order_id" value="<?= $row['order_id']; ?>">
                    <select name="status">
                        <option>Pending</option>
                        <option>Preparing</option>
                        <option>Ready</option>
                        <option>Completed</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No orders available.</p>
    <?php endif; ?>
</div>


<div id="menu-management" class="section"> 
    <h2>Menu Management</h2>

    <div class="form-container">
        <h3>Add New Menu Item</h3>
        <form method="POST">
            <input type="text" name="item_name" placeholder="Item Name" required>
            <input type="number" step="0.01" name="price" placeholder="Price (Ksh)" required>
            <input type="text" name="description" placeholder="Description">
            <button type="submit" name="add_item">Add Item</button>
        </form>
    </div>

    <div class="table-container">
        <h3>All Menu Items</h3>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Price (Ksh)</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php
            // Use PostgreSQL query
            $menuResult = pg_query($conn, "SELECT * FROM menu_items ORDER BY item_id DESC");
            while($menu = pg_fetch_assoc($menuResult)):
            ?>
            <tr>
                <form method="POST">
                    <td><?= $menu['item_id']; ?><input type="hidden" name="item_id" value="<?= $menu['item_id']; ?>"></td>
                    <td><input type="text" name="item_name" value="<?= $menu['item_name']; ?>" required></td>
                    <td><input type="number" step="0.01" name="price" value="<?= $menu['price']; ?>" required></td>
                    <td><input type="text" name="description" value="<?= $menu['description']; ?>"></td>
                    <td>
                        <button type="submit" name="update_item">Update</button>
                        <button type="submit" name="delete_item" onclick="return confirm('Are you sure?')">Delete</button>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</div>
</body>
</html>
