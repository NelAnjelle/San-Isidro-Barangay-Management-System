<?php
session_start();
include 'db_connect.php';

// Redirect if not an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch admin details
$stmt = $conn->prepare("SELECT first_name, last_name, suffix FROM users WHERE id = ? AND role = 'admin'");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Check if resident ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_residents.php");
    exit();
}
$resident_id = $_GET['id'];

// Fetch resident details
$stmt = $conn->prepare("SELECT username, email, first_name, middle_name, last_name, suffix, phone_number 
                        FROM users WHERE id = ? AND role = 'resident'");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$result = $stmt->get_result();
$resident = $result->fetch_assoc();
$stmt->close();

if (!$resident) {
    header("Location: admin_residents.php");
    exit();
}

// Handle form submission
$errors = [];
$success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $suffix = trim($_POST['suffix']);
    $phone_number = trim($_POST['phone_number']);
    $password = trim($_POST['password']);

    // Validation
    if (empty($username) || empty($email) || empty($first_name) || empty($last_name) || empty($phone_number)) {
        $errors[] = "Required fields cannot be empty.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!preg_match('/^\d{11}$/', $phone_number)) {
        $errors[] = "Phone number must be 11 digits.";
    }

    // Check for duplicate username or email (excluding current resident)
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $resident_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Username or email already exists.";
    }
    $stmt->close();

    if (empty($errors)) {
        if (!empty($password)) {
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, middle_name = ?, last_name = ?, suffix = ?, phone_number = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssssssi", $username, $email, $first_name, $middle_name, $last_name, $suffix, $phone_number, $hashed_password, $resident_id);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, middle_name = ?, last_name = ?, suffix = ?, phone_number = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $username, $email, $first_name, $middle_name, $last_name, $suffix, $phone_number, $resident_id);
        }
        if ($stmt->execute()) {
            $success = "Resident updated successfully.";
            header("Location: admin_residents.php?success=" . urlencode($success));
            exit();
        } else {
            $errors[] = "Failed to update resident.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resident - Barangay San Isidro Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="bg-green-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Barangay San Isidro Management System - Admin</h1>
            <div class="space-x-4">
                <span class="text-sm">Welcome, <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name'] . ($admin['suffix'] ? ' ' . $admin['suffix'] : '')); ?>!</span>
                <a href="admin_dashboard.php" class="hover:underline">Dashboard</a>
                <a href="admin_residents.php" class="hover:underline">Residents</a>
                <a href="admin_logout.php" class="hover:underline">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto mt-8 px-4 flex-grow">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Edit Resident</h2>
            <p class="text-gray-600 mb-6 text-center">Update resident information.</p>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($resident['username']); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($resident['email']); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($resident['first_name']); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($resident['middle_name'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($resident['last_name']); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="suffix" class="block text-sm font-medium text-gray-700">Suffix</label>
                    <input type="text" id="suffix" name="suffix" value="<?php echo htmlspecialchars($resident['suffix'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($resident['phone_number']); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password (optional)</label>
                    <input type="password" id="password" name="password" placeholder="Leave blank to keep current password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex justify-between">
                    <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Update Resident</button>
                    <a href="admin_residents.php" class="bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Barangay San Isidro Management System. All rights reserved.</p>
    </footer>
</body>
</html>