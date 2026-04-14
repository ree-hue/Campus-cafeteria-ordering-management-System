<?php
session_start();
include 'includes/db.php'; 


if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){
    echo "Access Denied!";
    exit();
}


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

// Use PostgreSQL query
$result = pg_query($conn, "SELECT * FROM menu_items ORDER BY item_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Menu Management</title>
    <style>
        
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin:0; padding:0; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; }
        .container { width: 90%; margin: 30px auto; }

        
        button { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; margin-top: 10px; transition: 0.2s; }
        .add-btn { background: #27ae60; color: white; }
        .update-btn { background: #3498db; color: white; }
        .delete-btn { background: #e74c3c; color: white; }
        .back-btn { background: #8e44ad; color: white; margin-bottom: 20px; display: inline-block; text-decoration: none; padding: 10px 20px; border-radius: 6px; }

        
        .form-container { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        input[type=text], input[type=number] { width: 100%; padding: 8px; margin: 5px 0; border-radius: 6px; border: 1px solid #ccc; }

        
        .table { width: 100%; border-collapse: collapse; background: white; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table th, .table td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        .table th { background: #ecf0f1; }

        
        button:hover { opacity: 0.85; }
        tr:hover { background: #f1f1f1; }
    </style>
</head>
<body>

<div class="header">🍔 Menu Management</div>

<div class="container">
    <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>

    
    <div class="form-container">
        <h3>Add New Menu Item</h3>
        <form method="POST">
            <input type="text" name="item_name" placeholder="Item Name" required>
            <input type="number" step="0.01" name="price" placeholder="Price (Ksh)" required>
            <input type="text" name="description" placeholder="Description">
            <button type="submit" name="add_item" class="add-btn">Add Item</button>
        </form>
    </div>

    
    <h3>All Menu Items</h3>
    <table class="table">
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Price (Ksh)</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while($row = pg_fetch_assoc($result)): ?>
        <tr>
            <form method="POST">
                <td><?php echo $row['item_id']; ?>
                    <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                </td>
                <td><input type="text" name="item_name" value="<?php echo $row['item_name']; ?>" required></td>
                <td><input type="number" step="0.01" name="price" value="<?php echo $row['price']; ?>" required></td>
                <td><input type="text" name="description" value="<?php echo $row['description']; ?>"></td>
                <td>
                    <button type="submit" name="update_item" class="update-btn">Update</button>
                    <button type="submit" name="delete_item" class="delete-btn" onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>