<?php
// Database credentials
$host = 'localhost'; // Usually 'localhost' for local development
$db   = 'user_db';    // Your database name from step 1
$user = 'root';      // Your database username (default for XAMPP/WAMP)
$pass = '';          // Your database password (default blank for XAMPP/WAMP)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Die with an error message if connection fails
     die("Connection failed: " . $e->getMessage());
}
?>