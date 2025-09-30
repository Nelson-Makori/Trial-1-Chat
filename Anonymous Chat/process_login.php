<?php
session_start();
require_once 'config/db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Debug information
    echo "Attempting login for email: " . htmlspecialchars($email) . "<br>";
    
    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: login.php");
        exit();
    }
    
    // Check if user exists
    $sql = "SELECT id, fullname, email, password FROM users WHERE email = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $fullname, $email, $hashed_password);
                if (mysqli_stmt_fetch($stmt)) {
                    echo "User found in database<br>";
                    if (password_verify($password, $hashed_password)) {
                        echo "Password verified successfully<br>";
                        // Password is correct, start a new session
                        session_start();
                        
                        // Store data in session variables
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["fullname"] = $fullname;
                        $_SESSION["email"] = $email;
                        
                        echo "Session variables set. Redirecting...<br>";
                        // Redirect to index page
                        header("Location: index.html");
                        exit();
                    } else {
                        echo "Password verification failed<br>";
                        $_SESSION['error'] = "Invalid email or password";
                    }
                }
            } else {
                echo "No user found with this email<br>";
                $_SESSION['error'] = "Invalid email or password";
            }
        } else {
            echo "Query execution failed: " . mysqli_error($conn) . "<br>";
            $_SESSION['error'] = "Oops! Something went wrong. Please try again later.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "Statement preparation failed: " . mysqli_error($conn) . "<br>";
    }
}

header("Location: login.php");
exit();
?> 