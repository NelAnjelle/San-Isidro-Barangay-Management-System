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

// Fetch all residents
$residents = [];
$result = $conn->query("SELECT id, username, first_name, last_name, suffix, email, phone_number FROM users WHERE role = 'resident' ORDER BY last_name ASC");
while ($row = $result->fetch_assoc()) {
    $residents[] = $row;
}

// Handle resident deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'resident'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_residents.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Management - Barangay San Isidro Management System</title>
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
                <a href="admin_logout.php" class="hover:underline">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto mt-8 px-4 flex-grow">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Resident Management</h2>
            <p class="text-gray-600 mb-6 text-center">View and manage resident records.</p>
            <?php if (empty($residents)): ?>
                <p class="text-gray-600 text-center">No residents found.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-4 border-b text-left">Name</th>
                                <th class="py-2 px-4 border-b text-left">Username</th>
                                <th class="py-2 px-4 border-b text-left">Email</th>
                                <th class="py-2 px-4 border-b text-left">Phone Number</th>
                                <th class="py-2 px-4 border-b text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($residents as $resident): ?>
                                <tr>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($resident['first_name'] . ' ' . ($resident['suffix'] ? $resident['suffix'] . ' ' : '') . $resident['last_name']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($resident['username']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($resident['email']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($resident['phone_number']); ?></td>
                                    <td class="py-2 px-4 border-b">
                                        <a href="admin_edit_resident.php?id=<?php echo $resident['id']; ?>" class="text-indigo-600 hover:underline">Edit</a>
                                        <a href="admin_residents.php?delete=<?php echo $resident['id']; ?>" class="text-red-600 hover:underline ml-4" onclick="return confirm('Are you sure you want to delete this resident?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Barangay San Isidro Management System. All rights reserved.</p>
    </footer>
</body>
</html>