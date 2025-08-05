<?php
session_start();
include 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $errors[] = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = 'admin'");
        if (!$stmt) {
            $errors[] = "Failed to prepare query: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['admin_id'] = $user['id'];
                        header("Location: admin_dashboard.php");
                        exit();
                    } else {
                        $errors[] = "Invalid username or password.";
                    }
                } else {
                    $errors[] = "Invalid username or not an admin account.";
                }
                $stmt->close();
            } else {
                $errors[] = "Query execution failed: " . $stmt->error;
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
    <title>Barangay Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
        }
        .input-focus {
            transition: all 0.3s ease;
        }
        .input-focus:focus {
            border-color: #15803d;
            box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.2);
        }
        .btn-hover {
            transition: all 0.3s ease;
        }
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md mx-4 animate-fadeIn">
        <div class="flex justify-center mb-6">
            <img src="https://via.placeholder.com/150x50?text=Barangay+Logo" alt="Barangay San Isidro Logo" class="h-12">
        </div>
        <h2 class="text-3xl font-extrabold text-gray-900 mb-6 text-center">Admin Login</h2>
        <p class="text-center text-gray-600 mb-6">Barangay San Isidro Management System</p>
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <?php foreach ($errors as $error): ?>
                    <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-5">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 input-focus"
                       placeholder="Enter your username">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 input-focus"
                       placeholder="Enter your password">
            </div>
            <button type="submit"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg btn-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Login
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">
            Resident? <a href="login.php" class="text-green-600 hover:underline font-medium">Login here</a>
        </p>
    </div>
</body>
</html>