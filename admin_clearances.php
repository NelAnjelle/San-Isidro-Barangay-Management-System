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

// Fetch certificate requests with user details
$requests = [];
$result = $conn->query("SELECT cr.id, cr.certificate_type, cr.purpose, cr.status, cr.created_at, u.first_name, u.last_name, u.suffix 
                        FROM certificate_requests cr 
                        JOIN users u ON cr.user_id = u.id 
                        ORDER BY cr.created_at DESC");
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    if (in_array($status, ['Pending', 'Approved', 'Rejected'])) {
        $stmt = $conn->prepare("UPDATE certificate_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_clearances.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Management - Barangay San Isidro Management System</title>
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
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Clearance Management</h2>
            <p class="text-gray-600 mb-6 text-center">Review and update certificate requests.</p>
            <?php if (empty($requests)): ?>
                <p class="text-gray-600 text-center">No certificate requests found.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-4 border-b text-left">Resident</th>
                                <th class="py-2 px-4 border-b text-left">Certificate Type</th>
                                <th class="py-2 px-4 border-b text-left">Purpose</th>
                                <th class="py-2 px-4 border-b text-left">Status</th>
                                <th class="py-2 px-4 border-b text-left">Date Requested</th>
                                <th class="py-2 px-4 border-b text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['first_name'] . ' ' . ($request['suffix'] ? $request['suffix'] . ' ' : '') . $request['last_name']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['certificate_type']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['purpose']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['status']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['created_at']); ?></td>
                                    <td class="py-2 px-4 border-b">
                                        <form method="POST" action="" class="inline">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <select name="status" class="border border-gray-300 rounded-md">
                                                <option value="Pending" <?php echo $request['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Approved" <?php echo $request['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="Rejected" <?php echo $request['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
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