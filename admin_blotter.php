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

// Fetch blotter reports with user details
$reports = [];
$result = $conn->query("SELECT br.id, br.incident_type, br.description, br.status, br.created_at, u.first_name, u.last_name, u.suffix 
                        FROM blotter_reports br 
                        JOIN users u ON br.user_id = u.id 
                        ORDER BY br.created_at DESC");
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $report_id = $_POST['report_id'];
    $status = $_POST['status'];
    if (in_array($status, ['Pending', 'In Progress', 'Resolved'])) {
        $stmt = $conn->prepare("UPDATE blotter_reports SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $report_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_blotter.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blotter Management - Barangay San Isidro Management System</title>
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
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Blotter Management</h2>
            <p class="text-gray-600 mb-6 text-center">Manage incident reports.</p>
            <?php if (empty($reports)): ?>
                <p class="text-gray-600 text-center">No blotter reports found.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-4 border-b text-left">Resident</th>
                                <th class="py-2 px-4 border-b text-left">Incident Type</th>
                                <th class="py-2 px-4 border-b text-left">Description</th>
                                <th class="py-2 px-4 border-b text-left">Status</th>
                                <th class="py-2 px-4 border-b text-left">Date Reported</th>
                                <th class="py-2 px-4 border-b text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($report['first_name'] . ' ' . ($report['suffix'] ? $report['suffix'] . ' ' : '') . $report['last_name']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($report['incident_type']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($report['description']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($report['status']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($report['created_at']); ?></td>
                                    <td class="py-2 px-4 border-b">
                                        <form method="POST" action="" class="inline">
                                            <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                            <select name="status" class="border border-gray-300 rounded-md">
                                                <option value="Pending" <?php echo $report['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="In Progress" <?php echo $report['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="Resolved" <?php echo $report['status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                            <button type="submit" class="text-indigo-600 hover:underline ml-2">Update</button>
                                        </form>
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