<?php
session_start();
include 'db_connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log to file
$log_file = 'test_login_debug.log';
function log_debug($message) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

log_debug("Test login started");

$errors = [];
$debug_info = [];

// Test database connection
if ($conn->connect_error) {
    $errors[] = "Database connection failed: " . $conn->connect_error;
    $debug_info[] = "Connection error: " . $conn->connect_error;
    log_debug("Connection error: " . $conn->connect_error);
} else {
    $debug_info[] = "Database connection successful";
    log_debug("Database connection successful");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    log_debug("Received: username='$username', password=[hidden]");

    if (empty($username) || empty($password)) {
        $errors[] = "Username and password are required.";
        $debug_info[] = "Input validation failed: empty fields";
        log_debug("Input validation failed: empty fields");
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        if (!$stmt) {
            $errors[] = "Failed to prepare query: " . $conn->error;
            $debug_info[] = "SQL Error: " . $conn->error;
            log_debug("SQL Error: " . $conn->error);
        } else {
            $stmt->bind_param("s", $username);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $debug_info[] = "Query executed, rows found: " . $result->num_rows;
                log_debug("Query executed, rows found: " . $result->num_rows);
                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    $debug_info[] = "User found: ID={$user['id']}, Username={$user['username']}, Role={$user['role']}";
                    log_debug("User found: ID={$user['id']}, Username={$user['username']}, Role={$user['role']}");
                    if ($user['role'] === 'admin') {
                        if (password_verify($password, $user['password'])) {
                            $_SESSION['admin_id'] = $user['id'];
                            $debug_info[] = "Password verified, session set: admin_id={$user['id']}";
                            log_debug("Password verified, session set: admin_id={$user['id']}");
                            header("Location: admin_dashboard.php");
                            exit();
                        } else {
                            $errors[] = "Invalid password.";
                            $debug_info[] = "Password verification failed";
                            log_debug("Password verification failed");
                        }
                    } else {
                        $errors[] = "User is not an admin.";
                        $debug_info[] = "Role check failed: Role is '{$user['role']}'";
                        log_debug("Role check failed: Role is '{$user['role']}'");
                    }
                } else {
                    $errors[] = "No user found with username: $username";
                    $debug_info[] = "No user found with username: '$username'";
                    log_debug("No user found with username: '$username'");
                }
                $stmt->close();
            } else {
                $errors[] = "Query execution failed: " . $stmt->error;
                $debug_info[] = "SQL Execution Error: " . $stmt->error;
                log_debug("SQL Execution Error: " . $stmt->error);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Test Admin Login</h2>
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($debug_info)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded">
                <p><strong>Debug Info:</strong></p>
                <?php foreach ($debug_info as $info): ?>
                    <p><?php echo htmlspecialchars($info); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Login</button>
        </form>
    </div>
</body>
</html>