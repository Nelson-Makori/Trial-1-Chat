<?php
require_once 'config/db_config.php';

// Test database connection
if ($conn) {
    echo "Database connection successful!<br>";
    
    // Try to create a test user
    $test_name = "Test User";
    $test_email = "test@example.com";
    $test_password = password_hash("test123", PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sss", $test_name, $test_email, $test_password);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Test user created successfully!<br>";
            echo "You can now login with:<br>";
            echo "Email: test@example.com<br>";
            echo "Password: test123<br>";
        } else {
            if (mysqli_errno($conn) == 1062) {
                echo "Test user already exists (that's okay)!<br>";
            } else {
                echo "Error creating test user: " . mysqli_error($conn) . "<br>";
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Test if we can read from the users table
    $sql = "SELECT * FROM users";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        echo "<br>Number of users in database: " . mysqli_num_rows($result) . "<br>";
        echo "<br>Users in database:<br>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "ID: " . $row['id'] . " | Name: " . $row['fullname'] . " | Email: " . $row['email'] . "<br>";
        }
    } else {
        echo "Error reading from database: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Database connection failed: " . mysqli_connect_error();
}
?> 