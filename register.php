<?php
include 'includes/db.php';

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if(!in_array($role, ['Student','Admin'])){
        $error = "Invalid role selected.";
    } else {
        // Use prepared statements to prevent SQL injection
        $query = "INSERT INTO users (name, email, password_hash, role, phone_number)
                  VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($conn, $query, array($name, $email, $password, $role, $phone));

        if($result){
            $success = "Registration successful. <a href='login.php'>Login here</a>";
        } else {
            $error = "Error: " . pg_last_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>User Registration</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body{
background:url('image/Cafeteria.jpeg') no-repeat center center fixed;
background-size:cover;
}


.background-container{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
}


.register-card{
background:rgba(255,255,255,0.95);
padding:40px;
border-radius:15px;
width:380px;
text-align:center;
box-shadow:0 8px 30px rgba(0,0,0,0.2);
}


.register-card h2{
margin-bottom:25px;
color:#333;
}


input, select{
width:100%;
padding:12px;
margin-bottom:15px;
border-radius:8px;
border:1px solid #ccc;
background:#f1f3f4;
font-size:14px;
}


.btn-register{
width:100%;
padding:12px;
background:#3498db;
color:white;
border:none;
border-radius:8px;
font-size:16px;
cursor:pointer;
transition:0.3s;
}

.btn-register:hover{
background:#2980b9;
}


.message{
margin-bottom:15px;
font-size:14px;
}

.success{color:green;}
.error{color:red;}

.login-link{
margin-top:15px;
font-size:14px;
}

.login-link a{
color:#3498db;
text-decoration:none;
font-weight:bold;
}

</style>

</head>

<body>

<div class="background-container">

<div class="register-card">

<h2>Register</h2>

<?php if(isset($success)) echo "<p class='message success'>$success</p>"; ?>
<?php if(isset($error)) echo "<p class='message error'>$error</p>"; ?>

<form method="POST">

<input type="text" name="name" placeholder="Name" required>

<input type="email" name="email" placeholder="Email" required>

<input type="text" name="phone" placeholder="Phone Number">

<input type="password" name="password" placeholder="Password" required>

<select name="role" required>
<option value="">--Select Role--</option>
<option value="Student">Student</option>
<option value="Admin">Admin</option>
</select>

<button type="submit" name="register" class="btn-register">Register</button>

</form>

<div class="login-link">
Already have an account? <a href="login.php">Login</a>
</div>

</div>

</div>

</body>
</html>