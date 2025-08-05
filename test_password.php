<?php
include 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$password = 'admin';
$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE id = ?");
$stmt->bind_param("i", 5);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    echo "User found: ID={$user['id']}, Username={$user['username']}<br>";
    echo "Stored hash: " . htmlspecialchars($user['password']) . "<br>";
    echo "Hash length: " . strlen($user['password']) . "<br>";
    if (password_verify($password, $user['password'])) {
        echo "Password verification successful!";
    } else {
        echo "Password verification failed.";
    }
} else {
    echo "No user found with ID=5.";
}
?>