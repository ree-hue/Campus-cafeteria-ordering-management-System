<?php
session_start();
include 'includes/db.php';

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $query = "SELECT * FROM users WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));

    if(pg_num_rows($result) > 0){
        $user = pg_fetch_assoc($result);

        if(password_verify($password, $user['password_hash'])){

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if($user['role'] == "Admin"){
                header("Location: admin_dashboard.php");
            }else{
                header("Location: student_dashboard.php");
            }
            exit();

        } else {
            $error = "Incorrect password";
        }

    } else {
        $error = "User not found";
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
