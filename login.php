<?php
session_start();
include 'includes/db.php';

if(isset($_POST['login'])){
    $email = trim(getPostData('email'));
    $password = getPostData('password');

    if(empty($email) || empty($password)){
        $error = "Please enter both email and password.";
    } elseif(!validateEmail($email)){
        $error = "Please enter a valid email address.";
    } else {
        // Prepare statement to prevent SQL injection
        $query = "SELECT user_id, name, email, password_hash, role FROM users WHERE email = $1";
        $result = pg_query_params($conn, $query, array($email));

        if(!$result){
            $error = "Database error. Please try again.";
            error_log("Login query failed: " . pg_last_error($conn));
        } elseif(pg_num_rows($result) > 0){
            $user = pg_fetch_assoc($result);

            if(password_verify($password, $user['password_hash'])){
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Debug: Log the role
                error_log("Login successful for user: " . $user['email'] . " with role: " . $user['role']);

                // Redirect based on role
                if($user['role'] == "Admin"){
                    header("Location: admin_dashboard.php");
                    exit();
                }else{
                    header("Location: student_dashboard.php");
                    exit();
                }
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with this email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cafeteria Login</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
display:flex;
justify-content:center;
align-items:center;
height:100vh;
background:url("image/Cover.jpeg");
background-size:cover;
background-position:center;
}

.login-box{
width:420px;
background:white;
padding:40px;
border-radius:10px;
box-shadow:0 5px 20px rgba(0,0,0,0.2);
text-align:center;
}

h2{
margin-bottom:25px;
font-size:32px;
}

input{
width:100%;
padding:15px;
margin-bottom:18px;
border-radius:8px;
border:1px solid #ddd;
background:#f5f5f5;
font-size:16px;
}

button{
width:100%;
padding:15px;
border:none;
border-radius:8px;
background:#3b82f6;
color:white;
font-size:18px;
cursor:pointer;
transition:0.3s;
}

button:hover{
background:#2563eb;
}

.error{
color:red;
margin-bottom:10px;
}

.register{
margin-top:20px;
font-size:15px;
}

.register a{
color:#3b82f6;
text-decoration:none;
font-weight:500;
}

.register a:hover{
text-decoration:underline;
}

</style>

</head>

<body>

<div class="login-box">

<h2>Login</h2>

<?php if(isset($error)){ ?>
<p class="error"><?php echo $error; ?></p>
<?php } ?>

<form method="POST">

<input type="email" name="email" placeholder="Email" required>

<input type="password" name="password" placeholder="Password" required>

<button type="submit" name="login">Login</button>

</form>

<p class="register">
Don't have an account? <a href="register.php">Register</a>
</p>

</div>

</body>
</html>
