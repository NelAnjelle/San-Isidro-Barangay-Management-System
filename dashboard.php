<?php
session_start();
include 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, suffix FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch recent certificate requests (limited to 3)
$certificate_requests = [];
$stmt = $conn->prepare("SELECT certificate_type, purpose, status, created_at FROM certificate_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $certificate_requests[] = $row;
}
$stmt->close();

// Fetch recent blotter reports (limited to 3)
$blotter_reports = [];
$stmt = $conn->prepare("SELECT incident_type, description, status, created_at FROM blotter_reports WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $blotter_reports[] = $row;
}
$stmt->close();

// Fetch recent service requests (limited to 3)
$service_requests = [];
$stmt = $conn->prepare("SELECT service_type, description, status, created_at FROM service_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $service_requests[] = $row;
}
$stmt->close();

// Fetch recent announcements (limited to 3)
$announcements = [];
$result = $conn->query("SELECT title, content, date FROM announcements ORDER BY date DESC LIMIT 3");
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

// Fetch recent activities (limited to 3)
$activities = [];
$result = $conn->query("SELECT name, description, date FROM activities ORDER BY date DESC LIMIT 3");
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Barangay San Isidro Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="bg-green-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Barangay San Isidro Management System</h1>
            <div class="space-x-4">
                <span class="text-sm">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ($user['suffix'] ? ' ' . $user['suffix'] : '')); ?>!</span>
                <a href="home.php" class="hover:underline">Home</a>
                <a href="profile.php" class="hover:underline">Profile</a>
                <a href="logout.php" class="hover:underline">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto mt-8 px-4 flex-grow">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Your Dashboard</h2>
            <p class="text-gray-600 mb-6 text-center">Manage your account and access barangay services.</p>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <a href="profile.php" class="bg-gray-50 p-4 rounded-md shadow-sm text-center hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Manage Profile</h3>
                    <p class="text-gray-600">Update your personal information.</p>
                </a>
                <a href="certificate.php" class="bg-gray-50 p-4 rounded-md shadow-sm text-center hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Request Certificates</h3>
                    <p class="text-gray-600">Apply for barangay clearances or certificates.</p>
                </a>
                <a href="blotter.php" class="bg-gray-50 p-4 rounded-md shadow-sm text-center hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Report Incidents</h3>
                    <p class="text-gray-600">Submit and track blotter reports.</p>
                </a>
                <a href="services.php" class="bg-gray-50 p-4 rounded-md shadow-sm text-center hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Service Requests</h3>
                    <p class="text-gray-600">Request barangay services.</p>
                </a>
                <a href="announcements.php" class="bg-gray-50 p-4 rounded-md shadow-sm text-center hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">View Announcements</h3>
                    <p class="text-gray-600">Stay updated with barangay news.</p>
                </a>
                <a href="activities.php" class="bg-gray-50 p-4 rounded-md shadow-sm text-center hover:bg-gray-100">
                    <h3 class="text-lg font-medium text-gray-700">Barangay Activities</h3>
                    <p class="text-gray-600">Explore community events.</p>
                </a>
            </div>

            <!-- Recent Activity -->
            <div class="space-y-8">
                <!-- Certificate Requests -->
                <div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Recent Certificate Requests</h3>
                    <?php if (empty($certificate_requests)): ?>
                        <p class="text-gray-600">No certificate requests found.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-4 border-b text-left">Certificate Type</th>
                                        <th class="py-2 px-4 border-b text-left">Purpose</th>
                                        <th class="py-2 px-4 border-b text-left">Status</th>
                                        <th class="py-2 px-4 border-b text-left">Date Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificate_requests as $request): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['certificate_type']); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['purpose']); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['status']); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="certificate.php" class="text-indigo-600 hover:underline mt-4 inline-block">View All Certificate Requests</a>
                    <?php endif; ?>
                </div>

                <!-- Blotter Reports -->
                <div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Recent Blotter Reports</h3>
                    <?php if (empty($blotter_reports)): ?>
                        <p class="text-gray-600">No blotter reports found.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-4 border-b text-left">Incident Type</th>
                                        <th class="py-2 px-4 border-b text-left">Description</th>
                                        <th class="py-2 px-4 border-b text-left">Status</th>
                                        <th class="py-2 px-4 border-b text-left">Date Reported</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blotter_reports as $report): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($report['incident_type']); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($report['description']); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($report['status']); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($report['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="blotter.php" class="text-indigo-600 hover:underline mt-4 inline-block">View All Blotter Reports</a>
                    <?php endif; ?>
                </div>

                <!-- Service Requests -->
                <div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Recent Service Requests</h3>
                    <?php if (empty($service_requests)): ?>
                        <p class="text-gray-600">No service requests found.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-4 border-b text-left">Service Type</th>
                                        <th class="py-2 px-4 border-b text-left">Description</th>
                                        <th class="py-2 px-4 border-b text-left">Status</th>
                                        <th class="py-2 px-4 border-b text-left">Date Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($service_requests as $request): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['service_type']); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['description']); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['status']); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="services.php" class="text-indigo-600 hover:underline mt-4 inline-block">View All Service Requests</a>
                    <?php endif; ?>
                </div>

                <!-- Announcements -->
                <div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Recent Announcements</h3>
                    <?php if (empty($announcements)): ?>
                        <p class="text-gray-600">No announcements available at this time.</p>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                                    <h4 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                    <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($announcement['content']); ?></p>
                                    <p class="text-sm text-gray-500 mt-2">Posted on: <?php echo htmlspecialchars($announcement['date']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="announcements.php" class="text-indigo-600 hover:underline mt-4 inline-block">View All Announcements</a>
                    <?php endif; ?>
                </div>

                <!-- Activities -->
                <div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Recent Activities</h3>
                    <?php if (empty($activities)): ?>
                        <p class="text-gray-600">No activities available at this time.</p>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($activities as $activity): ?>
                                <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                                    <h4 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($activity['name']); ?></h4>
                                    <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($activity['description']); ?></p>
                                    <p class="text-sm text-gray-500 mt-2">Date: <?php echo htmlspecialchars($activity['date']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="activities.php" class="text-indigo-600 hover:underline mt-4 inline-block">View All Activities</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Barangay San Isidro Management System. All rights reserved.</p>
    </footer>
</body>
</html>