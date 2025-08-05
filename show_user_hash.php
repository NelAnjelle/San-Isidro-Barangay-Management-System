<?php
include 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$log_file = 'show_user_hash_debug.log';
function log_debug($message) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

log_debug("Show user hash script started");

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "\n";
    log_debug("Connection failed: " . $conn->connect_error);
    exit();
}

log_debug("Database connection successful");

$result = $conn->query("DESCRIBE users");
if (!$result) {
    echo "Failed to get table structure: " . $conn->error . "\n";
    log_debug("Failed to get table structure: " . $conn->error);
    exit();
}

echo "Password Column Definition:\n";
echo "--------------------------\n";
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'password') {
        echo "Column: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}\n";
        log_debug("Password Column: Type={$row['Type']}, Null={$row['Null']}");
    }
}
echo "--------------------------\n\n";

$stmt = $conn->prepare("SELECT id, username, password, role, LENGTH(password) AS hash_length FROM users WHERE username = ?");
$username = 'admin1';
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

echo "User Data (admin1):\n";
echo "-------------------\n";
if ($user) {
    echo "ID: {$user['id']}\n";
    echo "Username: {$user['username']}\n";
    echo "Password Hash: {$user['password']}\n";
    echo "Hash Length: {$user['hash_length']}\n";
    echo "Role: {$user['role']}\n";
    echo "-------------------\n";
    log_debug("User found: ID={$user['id']}, Username={$user['username']}, Hash Length={$user['hash_length']}");
    if (password_verify('admin', $user['password'])) {
        echo "Password verification for 'admin': Successful\n";
    } else {
        echo "Password verification for 'admin': Failed\n";
    }
} else {
    echo "No user found with username 'admin1'.\n";
    log_debug("No user found with username 'admin1'");
}

$conn->close();
?>