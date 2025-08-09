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

// Fetch metrics
$total_residents = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'resident'")->fetch_row()[0];
$pending_clearances = $conn->query("SELECT COUNT(*) FROM certificate_requests WHERE status = 'Pending'")->fetch_row()[0];
$pending_blotters = $conn->query("SELECT COUNT(*) FROM blotter_reports WHERE status = 'Pending'")->fetch_row()[0];
$pending_services = $conn->query("SELECT COUNT(*) FROM service_requests WHERE status = 'Pending'")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg-nav {
            background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
        }
        .feature-hover {
            transition: all 0.3s ease;
        }
        .feature-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="gradient-bg-nav text-white p-4 shadow-md">
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
        <div class="bg-white p-8 rounded-2xl shadow-xl">
            <div class="flex justify-center mb-6">
                <img src="https://via.placeholder.com/150x50?text=Barangay+Logo" alt="Barangay San Isidro Logo" class="h-12">
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-6 text-center">Admin Dashboard</h2>
            <p class="text-center text-gray-600 mb-6">Manage barangay operations and services.</p>

            <!-- Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm text-center">
                    <h3 class="text-lg font-medium text-gray-700">Total Residents</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $total_residents; ?></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm text-center">
                    <h3 class="text-lg font-medium text-gray-700">Pending Clearances</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $pending_clearances; ?></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm text-center">
                    <h3 class="text-lg font-medium text-gray-700">Pending Blotter Reports</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $pending_blotters; ?></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm text-center">
                    <h3 class="text-lg font-medium text-gray-700">Pending Service Requests</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $pending_services; ?></p>
                </div>
            </div>

            <!-- Admin Features -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="admin_residents.php" class="bg-gray-50 p-4 rounded-lg shadow-sm text-center feature-hover hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Resident Management</h3>
                    <p class="text-gray-600 text-sm">Manage resident records.</p>
                </a>
                <a href="admin_clearances.php" class="bg-gray-50 p-4 rounded-lg shadow-sm text-center feature-hover hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Clearance Management</h3>
                    <p class="text-gray-600 text-sm">Review and approve certificate requests.</p>
                </a>
                <a href="admin_blotter.php" class="bg-gray-50 p-4 rounded-lg shadow-sm text-center feature-hover hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Blotter Management</h3>
                    <p class="text-gray-600 text-sm">Manage incident reports.</p>
                </a>
                <a href="admin_announcements.php" class="bg-gray-50 p-4 rounded-lg shadow-sm text-center feature-hover hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Announcement Management</h3>
                    <p class="text-gray-600 text-sm">Create and manage barangay announcements.</p>
                </a>
                <a href="admin_officials.php" class="bg-gray-50 p-4 rounded-lg shadow-sm text-center feature-hover hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Officials Management</h3>
                    <p class="text-gray-600 text-sm">Manage barangay officials' information.</p>
                </a>
                <a href="admin_other.php" class="bg-gray-50 p-4 rounded-lg shadow-sm text-center feature-hover hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Other Features</h3>
                    <p class="text-gray-600 text-sm">Access additional barangay tools.</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Barangay San Isidro Management System. All rights reserved.</p>
    </footer>
</body>
</html>