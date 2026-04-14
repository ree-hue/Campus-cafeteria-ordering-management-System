<?php
session_start();
include 'includes/db.php';

// Check if user is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin'){
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

// Add new admin
if(isset($_POST['add_admin'])){
    $name = $_POST['admin_name'] ?? '';
    $email = $_POST['admin_email'] ?? '';
    $phone = $_POST['admin_phone'] ?? '';
    $password = $_POST['admin_password'] ?? '';

    if(empty($name) || empty($email) || empty($password)){
        $error = "All fields are required.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (name, email, password_hash, role, phone_number)
                  VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($conn, $query, array($name, $email, $password_hash, 'Admin', $phone));

        if($result){
            $success = "Admin account created successfully. Email: $email";
            $_POST = array(); // Clear form
        } else {
            if(strpos(pg_last_error($conn), 'duplicate key') !== false){
                $error = "Email already exists.";
            } else {
                $error = "Error: " . pg_last_error($conn);
            }
        }
    }
}

// Get all admins
$admin_query = "SELECT user_id, name, email, phone_number, created_at FROM users WHERE role = 'Admin' ORDER BY created_at DESC";
$admin_result = pg_query($conn, $admin_query);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Admins</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    border-radius: 10px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    padding: 30px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header h1 {
    color: #333;
    font-size: 28px;
}

.back-btn {
    background: #95a5a6;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    transition: 0.3s;
}

.back-btn:hover {
    background: #7f8c8d;
}

.form-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.form-section h2 {
    color: #333;
    margin-bottom: 15px;
    font-size: 18px;
}

.form-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group input {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
}

.btn-submit {
    background: #667eea;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: 0.3s;
}

.btn-submit:hover {
    background: #5568d3;
}

.message {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
    font-weight: bold;
}

.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.admins-list {
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
}

.admins-list h2 {
    padding: 20px;
    background: #333;
    color: white;
    margin: 0;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table thead {
    background: #ecf0f1;
}

.admin-table th {
    padding: 15px;
    text-align: left;
    color: #333;
    font-weight: bold;
    border-bottom: 2px solid #ddd;
}

.admin-table td {
    padding: 15px;
    border-bottom: 1px solid #ddd;
}

.admin-table tbody tr:hover {
    background: #f1f3f5;
}

.empty-message {
    padding: 30px;
    text-align: center;
    color: #666;
}

@media (max-width: 768px) {
    .form-group {
        grid-template-columns: 1fr;
    }
    
    .admin-table {
        font-size: 14px;
    }
    
    .admin-table th, .admin-table td {
        padding: 10px;
    }
}
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Manage Admin Accounts</h1>
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <?php if($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-section">
        <h2>Add New Admin Account</h2>
        <form method="POST">
            <div class="form-group">
                <input type="text" name="admin_name" placeholder="Full Name" required>
                <input type="email" name="admin_email" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <input type="text" name="admin_phone" placeholder="Phone Number (Optional)">
                <input type="password" name="admin_password" placeholder="Password" required>
            </div>
            <button type="submit" name="add_admin" class="btn-submit">Create Admin Account</button>
        </form>
    </div>

    <div class="admins-list">
        <h2>All Admin Accounts</h2>
        <?php if(pg_num_rows($admin_result) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($admin = pg_fetch_assoc($admin_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['name']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo htmlspecialchars($admin['phone_number'] ?? 'N/A'); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($admin['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-message">No admin accounts found.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
