<?php
// 1. Include the database connection
include 'db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_btn'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic server-side validation
    if (empty($username) || empty($password)) {
        $error = "Please fill all fields.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // 2. Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // 3. Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);

            if ($stmt->rowCount() > 0) {
                $error = "Username is already taken.";
            } else {
                // 4. Insert the new user into the database
                $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $hashed_password]);

                // Redirect to login page upon successful registration
                $_SESSION['message'] = "Registration successful. Please log in.";
                header('Location: login.php');
                exit();
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
  <title>Register - Deeposts</title>
  <style>
    body { font-family: Poppins, sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
    .card { background:white; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); width:300px; text-align:center; }
    input { width:90%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px; }
    button { width:95%; padding:10px; background:#4a90e2; color:white; border:none; border-radius:6px; cursor:pointer; margin-top:10px; }
    button:hover { background:#357abd; }
    a { color:#4a90e2; text-decoration:none; font-size:0.9rem; }
    .error { color: red; margin-bottom: 15px; }
  </style>
</head>
<body>
  <div class="card">
    <img src="1.png" width="300" height="200">
    <h2>Register</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="register.php" method="post">
        <input type="text" name="username" placeholder="Choose a username" required>
        <input type="password" name="password" placeholder="Choose a password" required>
        <button type="submit" name="register_btn">Register</button>
    </form>
    
    <p>Already registered? <a href="login.php">Login here</a></p>
  </div>
  </body>
</html>