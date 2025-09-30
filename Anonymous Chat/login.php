<?php
// 1. Include the database connection
include 'db_connection.php';
session_start();

// Handle the registration success message if redirected from register.php
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic server-side validation
    if (empty($username) || empty($password)) {
        $error = "Please fill all fields.";
    } else {
        try {
            // 2. Retrieve user from the database
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // 3. Success: Start session and redirect
$_SESSION['loggedin'] = true;
$_SESSION['user_id'] = $user['id']; // Make sure 'id' is selected from the database
$_SESSION['username'] = $user['username'];
header('Location: welcome.php');
exit();
            } else {
                $error = "Invalid username or password.";
            }
        } catch (\PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Deeposts</title>
  <style>
    body { font-family: Poppins, sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
    .card { background:white; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); width:300px; text-align:center; }
    input { width:90%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px; }
    button { width:95%; padding:10px; background:#4a90e2; color:white; border:none; border-radius:6px; cursor:pointer; margin-top:10px; }
    button:hover { background:#357abd; }
    a { color:#4a90e2; text-decoration:none; font-size:0.9rem; }
    .error { color: red; margin-bottom: 15px; }
    .success { color: green; margin-bottom: 15px; }
  </style>
</head>
<body>
  <div class="card">
    <img src="1.png" width="300" height="200">
    <h2>Login</h2>

    <?php if (!empty($message)): ?>
        <p class="success"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="login.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login_btn">Login</button>
    </form>

    <p>No account? <a href="register.php">Register here</a></p>
  </div>
  </body>
</html>